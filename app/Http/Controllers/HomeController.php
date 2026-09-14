<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Province;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        /*
         * Lo último publicado, en la portada. Enseñar coches de verdad nada más
         * entrar dice más que cualquier frase, y de paso la portada deja de ser un
         * folleto: se puede empezar a mirar desde ahí.
         *
         * Sale de una sola consulta y se reparte sin repetirse: el primero es la
         * ficha que preside la portada y los seis siguientes, la rejilla de abajo.
         * Ver el mismo coche dos veces en la misma pantalla se nota enseguida.
         */
        $published = Car::query()
            ->published()
            ->with(['province', 'photos'])
            ->latest('id')
            ->take(9)
            ->get();

        $withPhoto = $published->filter(fn (Car $car) => $car->coverPhoto() !== null);

        /*
         * La ficha de la portada es el coche más reciente **que tenga foto**. Sobre
         * la foto van el nombre y el sitio en blanco, así que con el hueco de
         * relleno detrás —que es clarito— no habría manera de leerlos; y una
         * portada presidida por una silueta tampoco invita a nada.
         */
        $showcase = $withPhoto->first();

        return view('home', [
            'provinces' => Province::query()->orderBy('name')->get(),
            'showcase' => $showcase,
            'latest' => $published
                ->reject(fn (Car $car) => $showcase && $car->is($showcase))
                ->take(6)
                ->values(),

            // La tira que cruza la portada: sólo los que tienen foto, porque una
            // silueta dentro de la tira es un hueco a la vista de todos.
            'strip' => $withPhoto->values(),

            'stats' => [
                'cars' => Car::query()->published()->count(),
                'provinces' => Car::query()->published()->distinct()->count('province_code'),
            ],
        ]);
    }
}
