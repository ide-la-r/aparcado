<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Lo justo de la API de PayPal (Orders v2) que hace falta: crear una orden,
 * capturarla y comprobar la firma de un aviso.
 *
 * Todo esto vive en el servidor a propósito. En el TFG el pago se resolvía en el
 * navegador —`actions.order.create()` con el importe que hubiera en la página y
 * `actions.order.capture()` con un aviso bonito— así que el importe se cambiaba
 * desde las herramientas del navegador y del cobro no quedaba rastro en ninguna
 * parte.
 */
class PayPalClient
{
    private const SANDBOX = 'https://api-m.sandbox.paypal.com';

    private const LIVE = 'https://api-m.paypal.com';

    private const TIMEOUT = 20;

    public function configured(): bool
    {
        return filled(config('services.paypal.client_id'))
            && filled(config('services.paypal.secret'));
    }

    public function sandbox(): bool
    {
        return config('services.paypal.mode') !== 'live';
    }

    public function clientId(): ?string
    {
        return config('services.paypal.client_id');
    }

    /**
     * Crea la orden y devuelve su identificador.
     *
     * El importe llega en céntimos y se convierte aquí: PayPal quiere una cadena
     * con dos decimales y punto, y dejar eso al azar de un `number_format` por ahí
     * es cómo se cobran 3 € en lugar de 3,50 €.
     */
    public function createOrder(int $amountCents, string $reference, string $description): ?string
    {
        $response = $this->request()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $reference,
                'description' => mb_substr($description, 0, 127),
                'amount' => [
                    'currency_code' => $this->currency(),
                    'value' => number_format($amountCents / 100, 2, '.', ''),
                ],
            ]],
        ]);

        return $response->successful() ? $response->json('id') : null;
    }

    /**
     * Captura la orden y devuelve la respuesta entera. Quien llama tiene que
     * comprobar el importe: que la captura salga bien no significa que se haya
     * cobrado lo que se pedía.
     *
     * @return array<string, mixed>|null
     */
    public function captureOrder(string $orderId): ?array
    {
        $response = $this->request()->post("/v2/checkout/orders/{$orderId}/capture");

        return $response->successful() ? $response->json() : null;
    }

    /** @return array<string, mixed>|null */
    public function getOrder(string $orderId): ?array
    {
        $response = $this->request()->get("/v2/checkout/orders/{$orderId}");

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Devuelve el dinero de una captura.
     *
     * Hace falta para el único caso feo del flujo: dos personas pagando las mismas
     * fechas y una llegando segunda. Cobrar por algo que no se puede entregar y no
     * devolverlo es el peor fallo posible aquí, así que la devolución es parte del
     * cobro y no una tarea pendiente.
     */
    public function refundCapture(string $captureId): bool
    {
        return $this->request()
            ->post("/v2/payments/captures/{$captureId}/refund")
            ->successful();
    }

    /**
     * Le pregunta a PayPal si el aviso que acaba de llegar lo mandó PayPal.
     *
     * Sin esto, el endpoint de avisos es una puerta para que cualquiera mande un
     * «pagado» y se lleve un coche gratis: es la única comprobación que separa un
     * webhook de un formulario abierto al mundo.
     *
     * @param  array<string, string>  $headers
     */
    public function webhookIsAuthentic(array $headers, string $body): bool
    {
        $webhookId = config('services.paypal.webhook_id');

        if (blank($webhookId)) {
            return false;
        }

        $response = $this->request()->post('/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $headers['paypal-auth-algo'] ?? '',
            'cert_url' => $headers['paypal-cert-url'] ?? '',
            'transmission_id' => $headers['paypal-transmission-id'] ?? '',
            'transmission_sig' => $headers['paypal-transmission-sig'] ?? '',
            'transmission_time' => $headers['paypal-transmission-time'] ?? '',
            'webhook_id' => $webhookId,
            'webhook_event' => json_decode($body, true),
        ]);

        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }

    public function currency(): string
    {
        return config('services.paypal.currency', 'EUR');
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->timeout(self::TIMEOUT)
            ->withToken($this->token())
            ->acceptJson()
            ->asJson();
    }

    /**
     * El token vive unas ocho horas; se guarda casi todo ese rato para no pedir uno
     * nuevo en cada pago, con un margen por si el reloj no va fino.
     */
    private function token(): string
    {
        return Cache::remember('paypal.token', now()->addMinutes(400), function () {
            $response = Http::baseUrl($this->baseUrl())
                ->timeout(self::TIMEOUT)
                ->withBasicAuth(
                    (string) config('services.paypal.client_id'),
                    (string) config('services.paypal.secret'),
                )
                ->asForm()
                ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);

            return (string) $response->json('access_token');
        });
    }

    private function baseUrl(): string
    {
        return $this->sandbox() ? self::SANDBOX : self::LIVE;
    }
}
