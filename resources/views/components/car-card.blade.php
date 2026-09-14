@props(['car'])

{{-- Las fechas de la búsqueda viajan con el enlace, así que la ficha abre ya
     diciendo si está libre esos días y lo que cuesta. --}}
<a href="{{ route('cars.show', ['car' => $car, ...array_filter(request()->only(['from', 'to']))]) }}"
   class="card card-hover group block overflow-hidden">
    <div class="relative overflow-hidden">
        {{-- La foto se acerca un poco al pasar por encima. Es el gesto que hace que
             una rejilla de tarjetas apetezca recorrerla. --}}
        <x-car-photo :car="$car" class="aspect-[4/3] transition duration-500 group-hover:scale-[1.06]" />

        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-neutral-950/15 to-transparent opacity-0 transition duration-300 group-hover:opacity-100"></div>

        @if ($car->has_insurance)
            <span class="absolute top-3 left-3 z-30 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold text-neutral-800 shadow-sm backdrop-blur">
                <svg class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3Zm-1 14-4-4 1.4-1.4L11 13.2l4.6-4.6L17 10l-6 6Z" />
                </svg>
                A todo riesgo
            </span>
        @endif
    </div>

    <div class="p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="truncate font-display font-semibold text-neutral-900 transition group-hover:text-brand-700">
                    {{ $car->title() }}
                </h3>
                <p class="mt-0.5 truncate text-sm text-neutral-500">{{ $car->place() }}</p>
            </div>

            <p class="shrink-0 text-right">
                <span class="price text-xl">{{ $car->priceForHumans() }}</span>
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
