<x-layouts.app title="Coches">
    {{-- El catálogo no abre en blanco: abre con una franja oscura y el buscador
         montado a caballo entre las dos mitades. Lo que se viene a hacer aquí es
         buscar un coche, así que lo primero que se ve tiene que ser el sitio donde
         se pide. --}}
    <x-dark-section tone="soft">
        <div class="mx-auto max-w-6xl px-4 pt-12 pb-24 sm:px-6 sm:pt-16 sm:pb-28">
            <h1 class="text-3xl font-bold text-balance sm:text-4xl">
                Coches de gente que los tiene parados
            </h1>
            <p class="mt-3 max-w-lg text-white/60">
                Elige provincia y días. Sólo salen los que están libres justo esas fechas.
            </p>
        </div>
    </x-dark-section>

    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        {{-- `relative` no es decorativo: la franja de arriba lleva `relative` para
             colocar sus manchas de color, y un elemento posicionado se pinta por
             encima de todo lo que no lo está. Sin esto el buscador sube, sí, pero
             desaparece debajo de la franja. --}}
        <div class="relative z-10 -mt-16 sm:-mt-20">
            <x-car-search :provinces="$provinces" :filters="$filters" />
        </div>

        <div class="py-10">
            <p class="text-sm text-neutral-600">
                @if ($cars->total() === 0)
                    Ningún coche libre con esos datos.
                @else
                    <span class="font-semibold text-neutral-900">
                        {{ trans_choice(':count coche|:count coches', $cars->total(), ['count' => $cars->total()]) }}
                    </span>
                    @if ($filters['from'] ?? null)
                        libres {{ \App\Support\DateRange::forHumans($filters['from'], $filters['to']) }}
                    @endif
                    @if ($filters['province'] ?? null)
                        en {{ $provinces->firstWhere('code', $filters['province'])->name }}
                    @endif
                @endif
            </p>

            @if ($cars->isEmpty())
                <div class="card mt-6 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-neutral-300" viewBox="8 54 148 58" fill="currentColor" aria-hidden="true">
                        <path d="M18 96c-4 0-7-3-7-7v-9c0-5 3-9 8-11l13-4 12-19c3-5 8-8 14-8h50c6 0 11 3 14 8l12 19 13 4c5 2 8 6 8 11v9c0 4-3 7-7 7h-9a14 14 0 0 1-27 0H54a14 14 0 0 1-27 0h-9Z" />
                    </svg>

                    <p class="mt-4 font-display text-lg font-semibold">Aquí no hay nada.</p>
                    <p class="mx-auto mt-1.5 max-w-xs text-sm text-neutral-600">
                        Prueba con otras fechas, o quita la provincia para ver toda España.
                    </p>

                    <a href="{{ route('cars.index') }}" class="btn btn-primary mt-6">Ver todos los coches</a>
                </div>
            @else
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($cars as $index => $car)
                        {{-- El retraso se reinicia en cada fila: si creciera con la
                             posición, la última tarjeta de la página entraría casi dos
                             segundos después que la primera. --}}
                        <div data-reveal="{{ ($index % 3) * 70 }}">
                            <x-car-card :car="$car" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $cars->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
