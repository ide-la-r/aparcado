@props(['car'])

{{-- La tarjeta del catálogo, construida alrededor de la foto y no al revés: foto
     grande, el precio encima y debajo lo justo. Un catálogo de coches se mira, no
     se lee, y cada caja gris de más que se pone bajo la imagen lo acerca un poco
     más a un formulario.

     Las fechas de la búsqueda viajan con el enlace, así que la ficha abre ya
     diciendo si está libre esos días y lo que cuesta. --}}
<a href="{{ route('cars.show', ['car' => $car, ...array_filter(request()->only(['from', 'to']))]) }}"
   class="card card-hover group block overflow-hidden">
    <div class="relative overflow-hidden">
        {{-- La foto se acerca un poco al pasar por encima. Es el gesto que hace que
             una rejilla de tarjetas apetezca recorrerla. --}}
        <x-car-photo :car="$car" class="aspect-[3/2] transition duration-700 ease-out group-hover:scale-[1.07]" />

        <div class="photo-scrim z-30 opacity-90"></div>

        <p class="absolute bottom-3 left-3 z-30">
            <span class="price-tag transition duration-300 group-hover:bg-white">
                <span class="price text-base">{{ $car->priceForHumans() }}</span>
                <span class="text-[0.7rem] font-medium text-neutral-500">/día</span>
            </span>
        </p>

        @if ($car->has_insurance)
            <span class="absolute top-3 left-3 z-30 inline-flex items-center gap-1 rounded-full bg-white/92 px-2.5 py-1 text-xs font-semibold text-neutral-800 shadow-sm ring-1 ring-white/60 ring-inset backdrop-blur-md">
                <svg class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3Zm-1 14-4-4 1.4-1.4L11 13.2l4.6-4.6L17 10l-6 6Z" />
                </svg>
                A todo riesgo
            </span>
        @endif
    </div>

    <div class="p-4">
        <h3 class="truncate font-display text-[0.975rem] font-semibold text-neutral-900 transition group-hover:text-brand-700">
            {{ $car->title() }}
        </h3>

        <p class="mt-1 flex items-center gap-1 truncate text-sm text-neutral-600">
            <svg class="h-3.5 w-3.5 shrink-0 text-neutral-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z" />
            </svg>
            <span class="truncate">{{ $car->place() }}</span>
        </p>

        <p class="meta-line mt-3 border-t border-neutral-100 pt-3">
            <span>{{ $car->fuel }}</span>
            <span>{{ $car->transmission }}</span>
            <span>{{ $car->seats }} plazas</span>
        </p>
    </div>
</a>
