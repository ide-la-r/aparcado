<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchCarsRequest;
use App\Models\Car;
use App\Models\Province;
use App\Services\Bookings\Quote;
use App\Services\Catalogue\CarSearch;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CarController extends Controller
{
    public function index(SearchCarsRequest $request): View
    {
        $filters = $request->filters();

        return view('cars.index', [
            'cars' => CarSearch::fromFilters($filters)->paginate(),
            'provinces' => Province::query()->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(SearchCarsRequest $request, Car $car): View
    {
        // Un coche que su dueño ha escondido no existe para quien mira: si no, el
        // enlace que alguien guardó en favoritos lo seguiría enseñando.
        if (! $car->published) {
            throw new NotFoundHttpException;
        }

        $car->load(['owner', 'province', 'photos', 'features']);

        [$from, $to] = [$request->input('from'), $request->input('to')];
        $dated = filled($from) && filled($to);

        return view('cars.show', [
            'car' => $car,
            'from' => $from,
            'to' => $to,
            'quote' => $dated ? Quote::for($car, $from, $to) : null,
            'free' => $dated
                ? Car::query()->freeBetween($from, $to)->whereKey($car->id)->exists()
                : null,
            'groups' => config('aparcado.feature_groups'),
        ]);
    }
}
