<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Pasa a «terminada» lo que ya pasó. Una reserva pagada cuyo último día quedó
 * atrás no está en curso, y la lista de las dos partes tiene que decirlo sin que
 * nadie toque nada.
 *
 * Sigue ocupando sus fechas —el historial no se reescribe—, así que esto es sólo
 * para que se lea bien.
 */
class CloseFinishedBookingsCommand extends Command
{
    protected $signature = 'aparcado:close-bookings';

    protected $description = 'Marca como terminadas las reservas cuyo último día ya pasó';

    public function handle(): int
    {
        $closed = Booking::query()
            ->where('status', BookingStatus::Confirmed)
            ->where('ends_on', '<', Carbon::today()->toDateString())
            ->update([
                'status' => BookingStatus::Completed,
                'updated_at' => now(),
            ]);

        $this->info($closed === 0 ? 'No había ninguna que cerrar.' : "Cerradas {$closed}.");

        return self::SUCCESS;
    }
}
