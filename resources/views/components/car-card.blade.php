@props(['car'])

{{-- Las fechas de la búsqueda viajan con el enlace, así que la ficha abre ya
     diciendo si está libre esos días y lo que cuesta. --}}
<a href="{{ route('cars.show', ['car' => $car, ...array_filter(request()->only(['from', 'to']))]) }}"
   class="card group block overflow-hidden transition hover:ring-brand-300">
    <x-car-photo :car="$car" />

    <div class="p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="font-semibold text-neutral-900 group-hover:text-brand-700">{{ $car->title() }}</h3>
                <p class="mt-0.5 text-sm text-neutral-500">{{ $car->place() }}</p>
            </div>

            <p class="shrink-0 text-right">
                <span class="text-lg font-bold text-neutral-900">{{ $car->priceForHumans() }}</span>
                <span class="block text-xs text-neutral-500">al día</span>
            </p>
        </div>

        <div class="mt-3 flex flex-wrap gap-1.5">
            <span class="chip">{{ $car->fuel }}</span>
            <span class="chip">{{ $car->transmission }}</span>
            <span class="chip">{{ $car->seats }} plazas</span>
        </div>
    </div>
</a>
