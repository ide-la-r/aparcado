<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Las reglas del anuncio de un coche, para publicarlo y para editarlo. Es la misma
 * pantalla y las mismas reglas; lo único que cambia es que al editar la matrícula
 * no choca consigo misma.
 */
class CarRequest extends FormRequest
{
    public function rules(): array
    {
        $maxKb = (int) config('aparcado.uploads.max_kilobytes');
        $car = $this->route('car');

        return [
            'plate' => [
                'required', 'string', 'max:12',
                Rule::unique('cars', 'plate')->ignore($car?->id)->withoutTrashed(),
            ],

            'brand' => ['required', 'string', 'max:40'],
            'model' => ['required', 'string', 'max:40'],
            'registration_year' => ['required', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'kilometres' => ['required', 'integer', 'min:0', 'max:1500000'],

            'fuel' => ['required', Rule::in(config('aparcado.fuels'))],
            'transmission' => ['required', Rule::in(config('aparcado.transmissions'))],
            'body_type' => ['required', Rule::in(config('aparcado.body_types'))],
            'parking_type' => ['nullable', Rule::in(config('aparcado.parking_types'))],

            'colour' => ['required', 'string', 'max:30'],
            'seats' => ['required', 'integer', 'min:1', 'max:9'],
            'doors' => ['required', 'integer', 'min:2', 'max:5'],
            'power_hp' => ['required', 'integer', 'min:20', 'max:1000'],
            'has_insurance' => ['nullable', 'boolean'],

            // Ya en céntimos: lo convierte `prepareForValidation`, así que aquí se
            // valida el entero que se va a guardar y no el texto que se escribió.
            'price_cents' => ['required', 'integer', 'min:500', 'max:100000'],

            'description' => ['nullable', 'string', 'max:2000'],

            'address' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:60'],
            'province_code' => ['required', 'string', 'size:2', 'exists:provinces,code'],
            'postal_code' => ['required', 'string', 'regex:/^\d{5}$/'],

            'published' => ['nullable', 'boolean'],

            'features' => ['nullable', 'array'],
            'features.*' => ['integer', 'exists:features,id'],

            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}"],
        ];
    }

    public function messages(): array
    {
        return [
            'plate.unique' => 'Ya hay un coche publicado con esa matrícula.',
            'postal_code.regex' => 'El código postal son cinco cifras.',
            'price_cents.min' => 'El precio por día no puede bajar de 5 €.',
            'price_cents.max' => 'El precio por día no puede pasar de 1.000 €.',
            'price_cents.required' => 'Pon un precio por día.',
            'price_cents.integer' => 'El precio tiene que ser un número, como 35,50.',
            'photos.max' => 'Como mucho ocho fotos.',
        ];
    }

    public function attributes(): array
    {
        return [
            'plate' => 'la matrícula',
            'registration_year' => 'el año de matriculación',
            'kilometres' => 'los kilómetros',
            'fuel' => 'el combustible',
            'transmission' => 'el cambio',
            'body_type' => 'la carrocería',
            'power_hp' => 'la potencia',
            'province_code' => 'la provincia',
            'postal_code' => 'el código postal',
        ];
    }

    /** Lo que se guarda del coche, sin las fotos ni los extras, que van aparte. */
    public function carData(): array
    {
        return collect($this->validated())
            ->except(['features', 'photos'])
            ->put('has_insurance', $this->boolean('has_insurance'))
            ->put('published', $this->boolean('published'))
            ->all();
    }

    /** @return array<int, int> */
    public function featureIds(): array
    {
        return array_map('intval', $this->validated('features') ?? []);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            /*
             * La matrícula, en mayúsculas y con un solo espacio: si no, «1234 abc»
             * y «1234  ABC» son dos coches distintos para la regla de unicidad y el
             * mismo coche en la realidad.
             */
            'plate' => Str::upper(Str::squish((string) $this->input('plate'))),

            /*
             * De «35,50» a 3550. Si lo escrito no es un número se deja pasar tal
             * cual para que salte la regla `integer` con su mensaje —«tiene que ser
             * un número»— en lugar de `required`, que diría que falta el precio
             * cuando en realidad está mal escrito.
             */
            'price_cents' => Money::toCents($this->input('price')) ?? $this->input('price'),
        ]);
    }
}
