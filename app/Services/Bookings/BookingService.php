<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;

class BookingService
{
    /**
     * Pide un coche unos días. Nace **pendiente de pago**: no bloquea las fechas
     * hasta que se paga, así que pedir un coche y no pagarlo no lo saca del
     * catálogo. En el TFG cualquier reserva bloqueaba, y era la forma de tumbar al
     * de al lado sin gastar un euro.
     */
    public function request(User $renter, Car $car, string $from, string $to): Booking
    {
        $quote = Quote::for($car, $from, $to);

        /*
         * Si ya pidió este coche para estas mismas fechas, se le devuelve la misma
         * reserva en lugar de crear otra: recargar la página o darle dos veces al
         * botón no debería dejarle dos reservas que pagar.
         */
        $existing = $car->bookings()
            ->where('renter_id', $renter->id)
            ->where('status', BookingStatus::Pending)
            ->where('starts_on', $from)
            ->where('ends_on', $to)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $booking = new Booking([
            'starts_on' => $from,
            'ends_on' => $to,
            'days' => $quote->days,
            // El precio se congela aquí: si el dueño lo sube mañana, esta reserva
            // sigue costando lo que costaba.
            'price_cents_per_day' => $quote->pricePerDayCents,
            'total_cents' => $quote->totalCents,
            'status' => BookingStatus::Pending,
        ]);

        $booking->car()->associate($car);
        $booking->renter()->associate($renter);
        $booking->save();

        return $booking;
    }

    /** Marca una reserva como pagada. Desde aquí ya sí ocupa las fechas. */
    public function confirm(Booking $booking): Booking
    {
        // Por asignación directa: las marcas de tiempo se quedan fuera de
        // `$fillable` a propósito, que no las ponga un formulario.
        $booking->status = BookingStatus::Confirmed;
        $booking->confirmed_at = now();
        $booking->save();

        return $booking;
    }

    public function cancel(Booking $booking): Booking
    {
        $booking->status = BookingStatus::Cancelled;
        $booking->cancelled_at = now();
        $booking->save();

        return $booking;
    }
}
