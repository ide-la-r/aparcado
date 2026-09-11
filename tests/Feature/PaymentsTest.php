<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.paypal.client_id' => 'id-de-pruebas',
            'services.paypal.secret' => 'secreto-de-pruebas',
            'services.paypal.mode' => 'sandbox',
            'services.paypal.webhook_id' => 'webhook-de-pruebas',
        ]);
    }

    public function test_the_pay_screen_says_so_when_paypal_is_not_configured(): void
    {
        config(['services.paypal.client_id' => null, 'services.paypal.secret' => null]);

        $booking = $this->pendingBooking();

        // Sin credenciales la pantalla avisa, en lugar de reventar o de enseñar un
        // botón que no lleva a ninguna parte.
        $this->actingAs($booking->renter)
            ->get(route('payments.show', $booking))
            ->assertOk()
            ->assertSee('Los pagos no están configurados.');
    }

    public function test_only_the_renter_can_open_the_pay_screen(): void
    {
        $booking = $this->pendingBooking();

        $this->actingAs($booking->car->owner)->get(route('payments.show', $booking))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('payments.show', $booking))->assertForbidden();
        $this->actingAs($booking->renter)->get(route('payments.show', $booking))->assertOk();
    }

    public function test_a_cancelled_booking_cannot_be_paid(): void
    {
        $booking = $this->pendingBooking();
        $booking->update(['status' => BookingStatus::Cancelled]);

        $this->actingAs($booking->renter)->get(route('payments.show', $booking))->assertForbidden();
    }

    public function test_the_order_is_opened_with_the_amount_from_the_database(): void
    {
        $this->fakePayPal(orderId: 'ORDEN-1');

        $booking = $this->pendingBooking(totalCents: 20000);

        $this->actingAs($booking->renter)
            ->post(route('payments.order', $booking))
            ->assertOk()
            ->assertJson(['id' => 'ORDEN-1']);

        /*
         * Lo que de verdad importa: el importe que sale hacia PayPal es el de la
         * reserva guardada. En el TFG viajaba en la URL
         * (`iniciar_pago.php?precio_coche=…`), así que pagar 1 € por un fin de
         * semana era editar la barra de direcciones.
         */
        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/v2/checkout/orders')) {
                return false;
            }

            return $request['purchase_units'][0]['amount']['value'] === '200.00'
                && $request['purchase_units'][0]['amount']['currency_code'] === 'EUR';
        });

        $this->assertDatabaseHas('payments', [
            'provider_order_id' => 'ORDEN-1',
            'amount_cents' => 20000,
            'status' => PaymentStatus::Created->value,
        ]);
    }

    public function test_opening_the_screen_twice_does_not_open_two_orders(): void
    {
        $this->fakePayPal(orderId: 'ORDEN-1');

        $booking = $this->pendingBooking();

        $this->actingAs($booking->renter)->post(route('payments.order', $booking));
        $this->actingAs($booking->renter)->post(route('payments.order', $booking));

        $this->assertSame(1, Payment::query()->count());
    }

    public function test_paying_confirms_the_booking(): void
    {
        $this->fakePayPal(orderId: 'ORDEN-1', capturedValue: '200.00');

        $booking = $this->pendingBooking(totalCents: 20000);
        $payment = $this->openOrder($booking);

        $this->actingAs($booking->renter)
            ->postJson(route('payments.capture', $booking), ['order_id' => 'ORDEN-1'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(BookingStatus::Confirmed, $booking->refresh()->status);
        $this->assertTrue($payment->refresh()->isCaptured());
        $this->assertNotNull($payment->captured_at);
        $this->assertNotNull($payment->payload);
    }

    public function test_a_paid_booking_takes_the_dates(): void
    {
        $this->fakePayPal(orderId: 'ORDEN-1', capturedValue: '200.00');

        $booking = $this->pendingBooking(totalCents: 20000);
        $this->openOrder($booking);

        $this->actingAs($booking->renter)
            ->postJson(route('payments.capture', $booking), ['order_id' => 'ORDEN-1']);

        $this->assertFalse(
            Car::query()
                ->freeBetween($booking->starts_on->toDateString(), $booking->ends_on->toDateString())
                ->whereKey($booking->car_id)
                ->exists()
        );
    }

    public function test_a_different_amount_does_not_confirm_anything(): void
    {
        // PayPal dice que ha cobrado, pero ha cobrado 1 €.
        $this->fakePayPal(orderId: 'ORDEN-1', capturedValue: '1.00');

        $booking = $this->pendingBooking(totalCents: 20000);
        $payment = $this->openOrder($booking);

        $this->actingAs($booking->renter)
            ->postJson(route('payments.capture', $booking), ['order_id' => 'ORDEN-1'])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
        $this->assertSame(PaymentStatus::Failed, $payment->refresh()->status);
    }

    public function test_another_currency_does_not_confirm_anything_either(): void
    {
        $this->fakePayPal(orderId: 'ORDEN-1', capturedValue: '200.00', currency: 'USD');

        $booking = $this->pendingBooking(totalCents: 20000);
        $this->openOrder($booking);

        $this->actingAs($booking->renter)
            ->postJson(route('payments.capture', $booking), ['order_id' => 'ORDEN-1'])
            ->assertStatus(422);

        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    public function test_capturing_twice_does_not_charge_twice(): void
    {
        $this->fakePayPal(orderId: 'ORDEN-1', capturedValue: '200.00');

        $booking = $this->pendingBooking(totalCents: 20000);
        $this->openOrder($booking);

        $this->actingAs($booking->renter)->postJson(route('payments.capture', $booking), ['order_id' => 'ORDEN-1']);
        $this->actingAs($booking->renter)->postJson(route('payments.capture', $booking), ['order_id' => 'ORDEN-1'])
            ->assertOk()
            ->assertJson(['message' => 'Esta reserva ya estaba pagada.']);

        // Una sola llamada de captura a PayPal, aunque el navegador insista.
        Http::assertSentCount(3);
    }

    public function test_paypal_being_down_does_not_confirm_anything(): void
    {
        Http::fake(fn () => throw new ConnectionException('se ha ido la red'));

        $booking = $this->pendingBooking();

        $this->actingAs($booking->renter)
            ->post(route('payments.order', $booking))
            ->assertStatus(502);

        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_if_somebody_paid_those_dates_first_the_money_goes_back(): void
    {
        $this->fakePayPal(orderId: 'ORDEN-1', capturedValue: '200.00');

        $booking = $this->pendingBooking(totalCents: 20000);
        $this->openOrder($booking);

        // Entre abrir la orden y pagarla, otra persona paga esas mismas fechas.
        Booking::factory()
            ->for($booking->car)
            ->between($booking->starts_on->toDateString(), $booking->ends_on->toDateString())
            ->create();

        $this->actingAs($booking->renter)
            ->postJson(route('payments.capture', $booking), ['order_id' => 'ORDEN-1'])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        // Cobrar por algo que no se puede entregar y no devolverlo sería el peor
        // fallo posible aquí.
        Http::assertSent(fn ($request) => str_contains($request->url(), '/refund'));

        $this->assertSame(BookingStatus::Cancelled, $booking->refresh()->status);
    }

    public function test_an_unsigned_notice_is_not_believed(): void
    {
        Http::fake(['*/v1/oauth2/token' => Http::response(['access_token' => 'token'])]);
        Http::fake(['*/verify-webhook-signature' => Http::response(['verification_status' => 'FAILURE'])]);

        $booking = $this->pendingBooking();

        /*
         * Sin la comprobación de la firma, este endpoint sería un formulario abierto
         * al mundo para regalar coches: mandas un «pagado» y te llevas el coche.
         */
        $this->postJson(route('payments.webhook'), [
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['id' => 'ORDEN-1'],
        ])->assertForbidden();

        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
    }

    public function test_a_notice_without_a_configured_webhook_id_is_not_believed_either(): void
    {
        config(['services.paypal.webhook_id' => null]);

        $this->postJson(route('payments.webhook'), ['resource' => ['id' => 'ORDEN-1']])
            ->assertForbidden();
    }

    private function fakePayPal(string $orderId, ?string $capturedValue = null, string $currency = 'EUR'): void
    {
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'token-de-pruebas', 'expires_in' => 32400]),

            '*/v2/checkout/orders' => Http::response(['id' => $orderId, 'status' => 'CREATED'], 201),

            "*/v2/checkout/orders/{$orderId}/capture" => Http::response([
                'id' => $orderId,
                'status' => 'COMPLETED',
                'purchase_units' => [[
                    'payments' => ['captures' => [[
                        'id' => 'CAPTURA-1',
                        'amount' => ['value' => $capturedValue ?? '0.00', 'currency_code' => $currency],
                    ]]],
                ]],
            ]),

            '*/refund' => Http::response(['status' => 'COMPLETED'], 201),
        ]);
    }

    private function openOrder(Booking $booking): Payment
    {
        $this->actingAs($booking->renter)->post(route('payments.order', $booking));

        return $booking->payments()->firstOrFail();
    }

    private function pendingBooking(int $totalCents = 20000): Booking
    {
        $car = Car::factory()->create(['price_cents' => intdiv($totalCents, 5)]);

        return Booking::factory()
            ->for($car)
            ->pending()
            ->between(
                Carbon::today()->addDays(10)->toDateString(),
                Carbon::today()->addDays(14)->toDateString(),
            )
            ->create(['total_cents' => $totalCents, 'price_cents_per_day' => intdiv($totalCents, 5)]);
    }
}
