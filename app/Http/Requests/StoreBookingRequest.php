<?php

namespace App\Http\Requests;

use App\Models\Car;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreBookingRequest extends FormRequest
{
    public function rules(): array
    {
        $maxMonths = (int) config('aparcado.bookings.max_months_ahead');

        return [
            'from' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.Carbon::today()->addMonths($maxMonths)->toDateString(),
            ],

            'to' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:from',
                $this->withinTheAllowedLength(),
                $this->stillFree(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'from.after_or_equal' => 'No se puede alquilar un coche en el pasado.',
            'to.after_or_equal' => 'El día de salida no puede ser anterior al de entrada.',
        ];
    }

    public function car(): Car
    {
        return $this->route('car');
    }

    private function withinTheAllowedLength(): Closure
    {
        $maxDays = (int) config('aparcado.bookings.max_days');

        return function (string $attribute, mixed $value, Closure $fail) use ($maxDays) {
            $from = $this->input('from');

            if (! $from || ! $value) {
                return;
            }

            $days = Carbon::parse($from)->startOfDay()->diffInDays(Carbon::parse($value)->startOfDay()) + 1;

            if ($days > $maxDays) {
                $fail("Un alquiler no puede durar más de {$maxDays} días.");
            }
        };
    }

    /**
     * Se vuelve a comprobar aquí, y no sólo en la ficha: entre que alguien mira un
     * coche y le da a reservar puede pasar un rato largo, y en ese rato otra
     * persona puede haber pagado esas mismas fechas.
     */
    private function stillFree(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            $from = $this->input('from');

            if (! $from || ! $value) {
                return;
            }

            $free = Car::query()
                ->freeBetween($from, $value)
                ->whereKey($this->car()->id)
                ->exists();

            if (! $free) {
                $fail('Alguien se ha adelantado: el coche ya está cogido esos días.');
            }
        };
    }
}
