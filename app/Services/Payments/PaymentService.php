<?php

namespace App\Services\Payments;

use App\Enums\CaptureOutcome;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Payment;
use App\Services\Bookings\BookingService;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly PayPalClient $paypal,
        private readonly BookingService $bookings,
    ) {}

    /**
     * Abre el cobro de una reserva: crea la orden en PayPal con el importe que dice
     * **la base de datos** y guarda la fila que luego permite comprobarlo todo.
     *
     * El importe no se recibe nunca del navegador. En el TFG viajaba en la URL
     * (`iniciar_pago.php?precio_coche=…`), así que pagar 1 € por un fin de semana
     * era editar la barra de direcciones.
     */
    public function start(Booking $booking): ?Payment
    {
        if (! $this->paypal->configured()) {
            return null;
        }

        // Si ya hay una orden abierta para esta reserva, se reutiliza: recargar la
        // pantalla de pago no debería dejar órdenes sueltas en PayPal.
        $open = $booking->payments()
            ->where('status', PaymentStatus::Created)
            ->latest('id')
            ->first();

        if ($open !== null) {
            return $open;
        }

        try {
            $orderId = $this->paypal->createOrder(
                $booking->total_cents,
                "reserva-{$booking->id}",
                "Alquiler de {$booking->car->title()}, {$booking->datesForHumans()}",
            );
        } catch (Throwable $exception) {
            Log::warning('PayPal no ha contestado al crear la orden', [
                'booking' => $booking->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if ($orderId === null) {
            return null;
        }

        $payment = new Payment;
        $payment->forceFill([
            'provider' => 'paypal',
            'provider_order_id' => $orderId,
            'amount_cents' => $booking->total_cents,
            'currency' => $this->paypal->currency(),
            'status' => PaymentStatus::Created,
        ]);
        $payment->user()->associate($booking->renter);
        $payment->payable()->associate($booking);
        $payment->save();

        return $payment;
    }

    /**
     * Cobra de verdad y sólo confirma la reserva si todo cuadra: que PayPal diga
     * que ha cobrado no basta, hay que comprobar **cuánto** ha cobrado.
     */
    public function capture(Payment $payment): CaptureOutcome
    {
        if ($payment->isCaptured()) {
            return CaptureOutcome::AlreadyCaptured;
        }

        try {
            $response = $this->paypal->captureOrder($payment->provider_order_id);
        } catch (Throwable $exception) {
            Log::warning('PayPal no ha contestado al capturar', [
                'payment' => $payment->id,
                'error' => $exception->getMessage(),
            ]);

            return CaptureOutcome::GatewayError;
        }

        if ($response === null) {
            return CaptureOutcome::GatewayError;
        }

        $capture = data_get($response, 'purchase_units.0.payments.captures.0', []);
        $cents = (int) round(((float) data_get($capture, 'amount.value', 0)) * 100);
        $currency = data_get($capture, 'amount.currency_code');

        if ($cents !== $payment->amount_cents || $currency !== $payment->currency) {
            $this->markFailed($payment, $response);

            Log::error('El importe cobrado no coincide con el pedido', [
                'payment' => $payment->id,
                'esperado' => $payment->amount_cents,
                'cobrado' => $cents,
            ]);

            return CaptureOutcome::AmountMismatch;
        }

        $payment->forceFill([
            'status' => PaymentStatus::Captured,
            'captured_at' => now(),
            'payload' => $response,
        ])->save();

        $booking = $payment->payable;

        /*
         * Última comprobación, y la más incómoda: entre que se abrió la orden y se
         * pagó, otra persona puede haber pagado esas mismas fechas. Aquí el dinero
         * ya está cobrado, así que lo correcto es devolverlo, no quedárselo.
         */
        if ($booking instanceof Booking && ! $this->stillFree($booking)) {
            $this->bookings->cancel($booking);

            return $this->refund($payment, data_get($capture, 'id'));
        }

        if ($booking instanceof Booking) {
            $this->bookings->confirm($booking);
        }

        return CaptureOutcome::Captured;
    }

    private function stillFree(Booking $booking): bool
    {
        return Car::query()
            ->freeBetween($booking->starts_on->toDateString(), $booking->ends_on->toDateString())
            ->whereKey($booking->car_id)
            ->exists();
    }

    private function refund(Payment $payment, ?string $captureId): CaptureOutcome
    {
        if ($captureId === null) {
            return CaptureOutcome::DatesTakenRefundFailed;
        }

        try {
            $refunded = $this->paypal->refundCapture($captureId);
        } catch (Throwable $exception) {
            $refunded = false;
        }

        Log::warning('Reserva pagada con las fechas ya cogidas', [
            'payment' => $payment->id,
            'devuelto' => $refunded,
        ]);

        return $refunded
            ? CaptureOutcome::DatesTakenAndRefunded
            : CaptureOutcome::DatesTakenRefundFailed;
    }

    private function markFailed(Payment $payment, array $payload): void
    {
        $payment->forceFill([
            'status' => PaymentStatus::Failed,
            'payload' => $payload,
        ])->save();
    }
}
