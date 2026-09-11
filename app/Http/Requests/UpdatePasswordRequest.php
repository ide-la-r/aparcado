<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // `current_password` la comprueba Laravel contra la contraseña que hay
            // guardada: cambiarla exige saber la de antes, para que una sesión
            // abierta en un ordenador ajeno no deje echar al dueño de su cuenta.
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Esa no es tu contraseña de ahora.',
            'password.confirmed' => 'Las dos contraseñas nuevas no coinciden.',
        ];
    }
}
