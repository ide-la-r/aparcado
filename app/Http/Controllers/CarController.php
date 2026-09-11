<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchCarsRequest;
use App\Models\Province;
use App\Services\Catalogue\CarSearch;
use Illuminate\View\View;

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
}
