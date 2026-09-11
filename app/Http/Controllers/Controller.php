<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Para poder llamar a `$this->authorize()`: desde Laravel 11 el controlador
    // base ya no lo trae puesto.
    use AuthorizesRequests;
}
