<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\View\View;

class CarController extends Controller
{
    public function index(): View
    {
        $cars = Car::query()
            ->published()
            // Con las relaciones cargadas de golpe: la tarjeta pinta la provincia y
            // la primera foto de cada coche, y sin esto serían dos consultas por
            // coche en la rejilla.
            ->with(['province', 'photos'])
            ->latest('id')
            ->paginate(12);

        return view('cars.index', ['cars' => $cars]);
    }
}
