<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class SearchCarsRequest extends FormRequest
{
    public function rules(): array
    {
        $maxMonths = (int) config('aparcado.bookings.max_months_ahead');

        return [
            'province' => ['nullable', 'string', 'size:2', 'exists:provinces,code'],

            'from' => [
                'nullable',
                'required_with:to',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.Carbon::today()->addMonths($maxMonths)->toDateString(),
            ],

            'to' => [
                'nullable',
                'required_with:from',
                'date_format:Y-m-d',
                'after_or_equal:from',
                $this->withinTheAllowedLength(),
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

    /** Los filtros ya limpios, tal como los espera la búsqueda. */
    public function filters(): array
    {
        return $this->only(['province', 'from', 'to']);
    }

    protected function prepareForValidation(): void
    {
        /*
         * Un formulario por GET manda los campos vacíos como cadena vacía, no como
         * nulo, y `nullable` los deja pasar: la búsqueda acabaría filtrando por una
         * provincia que es ''. Aquí se convierten en nulos antes de validar.
         */
        $this->merge(
            collect($this->only(['province', 'from', 'to']))
                ->map(fn (mixed $value) => filled($value) ? $value : null)
                ->all()
        );
    }

    /**
     * Un alquiler no puede durar más de lo que diga la configuración. Va como regla
     * propia porque el tope depende del día de entrada, y eso no se puede escribir
     * en un `before_or_equal`.
     */
    private function withinTheAllowedLength(): Closure
    {
        $maxDays = (int) config('aparcado.bookings.max_days');

        return function (string $attribute, mixed $value, Closure $fail) use ($maxDays) {
            $from = $this->input('from');

            if (! $from || ! $value) {
                return;
            }

            $days = Carbon::parse($from)->startOfDay()
                ->diffInDays(Carbon::parse($value)->startOfDay()) + 1;

            if ($days > $maxDays) {
                $fail("Un alquiler no puede durar más de {$maxDays} días.");
            }
        };
    }
}
