<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use App\Services\Payments\PayPalClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly PayPalClient $paypal,
    ) {}

    /** La pantalla de pago de una reserva. */
    public function show(Booking $booking): View
    {
        $this->authorize('pay', $booking);

        $booking->load(['car.owner', 'car.province', 'car.photos']);

        return view('payments.show', [
            'booking' => $booking,
            'configured' => $this->paypal->configured(),
            'sandbox' => $this->paypal->sandbox(),
            'clientId' => $this->paypal->clientId(),
        ]);
    }

    /**
     * Abre la orden en PayPal. Lo llama el botón de PayPal desde el navegador, y
     * devuelve sólo el identificador: el importe se queda aquí.
     */
    public function createOrder(Booking $booking): JsonResponse
    {
        $this->authorize('pay', $booking);

        $payment = $this->payments->start($booking);

        if ($payment === null) {
            return response()->json(['error' => 'No hemos podido abrir el pago.'], 502);
        }

        return response()->json(['id' => $payment->provider_order_id]);
    }

    /**
     * Cobra. El navegador manda el identificador de la orden y nada más: el importe
     * y la reserva salen de nuestra propia fila, así que no hay nada que manipular
     * desde fuera.
     */
    public function capture(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('pay', $booking);

        $payment = $booking->payments()
            ->where('provider_order_id', (string) $request->input('order_id'))
            ->firstOrFail();

        $outcome = $this->payments->capture($payment);

        // El mensaje se deja en la sesión para que salga en «Mis reservas», que es
        // adonde va el navegador después.
        $request->session()->flash($outcome->isGood() ? 'status' : 'warning', $outcome->message());

        return response()->json([
            'ok' => $outcome->isGood(),
            'message' => $outcome->message(),
            'redirect' => route('bookings.index'),
        ], $outcome->isGood() ? 200 : 422);
    }

    /**
     * El aviso de PayPal, para cuando el navegador se cierra a mitad del cobro y la
     * vuelta nunca llega. Primero se comprueba la firma: sin eso, esto es un
     * formulario abierto al mundo para regalar coches.
     */
    public function webhook(Request $request): JsonResponse
    {
        $headers = collect($request->headers->all())
            ->map(fn (array $values) => $values[0] ?? '')
            ->all();

        if (! $this->paypal->webhookIsAuthentic($headers, $request->getContent())) {
            Log::warning('Aviso de PayPal con firma que no cuadra');

            return response()->json(['ok' => false], 403);
        }

        $orderId = (string) $request->input('resource.supplementary_data.related_ids.order_id')
            ?: (string) $request->input('resource.id');

        $payment = Payment::query()->where('provider_order_id', $orderId)->first();

        if ($payment !== null && ! $payment->isCaptured()) {
            $this->payments->capture($payment);
        }

        return response()->json(['ok' => true]);
    }
}
