<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Province;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'provinces' => Province::query()->orderBy('name')->get(),

            /*
             * Lo último publicado, en la portada. Enseñar coches de verdad nada más
             * entrar dice más que cualquier frase, y de paso la portada deja de ser
             * un folleto: se puede empezar a mirar desde ahí.
             */
            'latest' => Car::query()
                ->published()
                ->with(['province', 'photos'])
                ->latest('id')
                ->take(3)
                ->get(),

            'stats' => [
                'cars' => Car::query()->published()->count(),
                'provinces' => Car::query()->published()->distinct()->count('province_code'),
            ],
        ]);
    }
}
