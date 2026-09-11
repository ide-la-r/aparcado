<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        $minAge = (int) config('aparcado.min_age');

        return [
            'name' => ['required', 'string', 'max:60'],
            'surname' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => ['required', 'string', 'max:20'],

            // Hay que poder conducir: en el TFG no se comprobaba la edad en ningún
            // sitio, y cualquiera podía registrarse y reservar.
            'birthdate' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:'.Carbon::today()->subYears($minAge)->toDateString(),
            ],

            'document_type' => ['required', Rule::in(config('aparcado.document_types'))],
            'document_number' => ['required', 'string', 'max:30', 'unique:users,document_number'],
        ];
    }

    public function messages(): array
    {
        return [
            'birthdate.before_or_equal' => 'Hay que tener '.config('aparcado.min_age').' años o más.',
            'document_number.unique' => 'Ya hay una cuenta con ese documento.',
            'password.confirmed' => 'Las dos contraseñas no coinciden.',
        ];
    }
}
