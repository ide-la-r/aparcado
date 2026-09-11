<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        $minAge = (int) config('aparcado.min_age');
        $maxKb = (int) config('aparcado.uploads.max_kilobytes');

        return [
            'name' => ['required', 'string', 'max:60'],
            'surname' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:20'],

            'email' => [
                'required', 'string', 'email', 'max:120',
                // Ignorando la propia cuenta: si no, guardar sin tocar el correo
                // daría «ya hay una cuenta con ese correo».
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],

            'birthdate' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:'.Carbon::today()->subYears($minAge)->toDateString(),
            ],

            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKb}"],
        ];
    }

    public function messages(): array
    {
        return [
            'birthdate.before_or_equal' => 'Hay que tener '.config('aparcado.min_age').' años o más.',
        ];
    }
}
