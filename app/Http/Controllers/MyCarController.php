<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
use App\Models\Car;
use App\Models\CarPhoto;
use App\Models\Feature;
use App\Models\Province;
use App\Services\Geocoding\Geocoder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MyCarController extends Controller
{
    public function __construct(private readonly Geocoder $geocoder) {}

    public function index(Request $request): View
    {
        $cars = $request->user()
            ->cars()
            ->with(['province', 'photos'])
            ->withCount('bookings')
            ->latest('id')
            ->get();

        return view('my-cars.index', ['cars' => $cars]);
    }

    public function create(): View
    {
        return view('my-cars.create', $this->formData());
    }

    public function store(CarRequest $request): RedirectResponse
    {
        $car = $request->user()->cars()->create(
            [...$request->carData(), ...$this->coordinatesFor($request)]
        );

        $car->features()->sync($request->featureIds());
        $this->storePhotos($car, $request);

        return redirect()
            ->route('my-cars.index')
            ->with('status', "Publicado. Ya se puede ver {$car->title()} en el catálogo.");
    }

    public function edit(Car $car): View
    {
        $this->authorize('update', $car);

        $car->load(['features', 'photos']);

        return view('my-cars.edit', [...$this->formData(), 'car' => $car]);
    }

    public function update(CarRequest $request, Car $car): RedirectResponse
    {
        $this->authorize('update', $car);

        $data = $request->carData();

        /*
         * Sólo se vuelve a geocodificar cuando cambia la dirección. Si no, editar el
         * precio pediría coordenadas a Photon otra vez sin ninguna razón, y una
         * respuesta peor podría mover un coche que estaba bien puesto.
         */
        if ($this->addressChanged($car, $data)) {
            $data = [...$data, ...$this->coordinatesFor($request)];
        }

        $car->update($data);
        $car->features()->sync($request->featureIds());
        $this->storePhotos($car, $request);

        return back()->with('status', 'Guardado.');
    }

    public function destroy(Car $car): RedirectResponse
    {
        $this->authorize('delete', $car);

        /*
         * Borrado suave: las reservas que ya hubo siguen apuntando a este coche y el
         * historial de las dos partes no puede quedarse colgando. Las fotos también
         * se quedan, por lo mismo.
         */
        $car->delete();

        return redirect()->route('my-cars.index')->with('status', 'Coche retirado.');
    }

    public function destroyPhoto(Car $car, CarPhoto $photo): RedirectResponse
    {
        $this->authorize('update', $car);

        abort_unless($photo->car_id === $car->id, 404);

        Storage::disk(config('aparcado.uploads.cars_disk'))->delete($photo->path);
        $photo->delete();

        return back()->with('status', 'Foto quitada.');
    }

    private function formData(): array
    {
        return [
            'provinces' => Province::query()->orderBy('name')->get(),
            'features' => Feature::query()->orderBy('position')->get()->groupBy('group'),
            'groups' => config('aparcado.feature_groups'),
        ];
    }

    /** @return array{latitude?: float, longitude?: float} */
    private function coordinatesFor(CarRequest $request): array
    {
        $found = $this->geocoder->locate(
            $request->validated('address'),
            $request->validated('postal_code'),
            $request->validated('city'),
        );

        // Si Photon no contesta, el coche se guarda sin coordenadas y su ficha sale
        // sin mapa. Perder el mapa es un detalle; perder el anuncio, no.
        return $found === null
            ? []
            : ['latitude' => $found['lat'], 'longitude' => $found['lon']];
    }

    private function addressChanged(Car $car, array $data): bool
    {
        foreach (['address', 'postal_code', 'city', 'province_code'] as $field) {
            if ($car->{$field} !== $data[$field]) {
                return true;
            }
        }

        return false;
    }

    private function storePhotos(Car $car, CarRequest $request): void
    {
        $files = $request->file('photos') ?? [];

        if ($files === []) {
            return;
        }

        // Las nuevas van detrás de las que ya hay: la primera foto es la portada del
        // catálogo, y subir una más no debería cambiarla sin avisar.
        $position = (int) $car->photos()->max('position') + 1;

        foreach ($files as $file) {
            $car->photos()->create([
                'path' => $file->store('cars', config('aparcado.uploads.cars_disk')),
                'position' => $position++,
            ]);
        }
    }
}
