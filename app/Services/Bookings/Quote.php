<?php

namespace App\Services\Bookings;

use App\Models\Car;
use App\Support\Money;
use Illuminate\Support\Carbon;

/**
 * Lo que cuesta alquilar un coche unos días concretos. Se calcula **siempre en el
 * servidor** a partir del precio que tiene el coche guardado: en el TFG el importe
 * viajaba en la URL hasta PayPal (`iniciar_pago.php?precio_coche=…`), así que
 * cambiarlo era editar la barra de direcciones.
 */
final class Quote
{
    private function __construct(
        public readonly int $days,
        public readonly int $pricePerDayCents,
        public readonly int $totalCents,
    ) {}

    public static function for(Car $car, string $from, string $to): self
    {
        // El día de entrega cuenta: de lunes a lunes es un día, no cero.
        $days = Carbon::parse($from)->startOfDay()
            ->diffInDays(Carbon::parse($to)->startOfDay()) + 1;

        return new self(
            days: $days,
            pricePerDayCents: $car->price_cents,
            totalCents: $car->price_cents * $days,
        );
    }

    public function totalForHumans(): string
    {
        return Money::format($this->totalCents);
    }

    public function breakdownForHumans(): string
    {
        return trans_choice(':count día|:count días', $this->days, ['count' => $this->days])
            .' × '.Money::short($this->pricePerDayCents);
    }
}
