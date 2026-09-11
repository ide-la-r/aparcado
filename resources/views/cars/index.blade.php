<x-layouts.app title="Coches">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Coches disponibles</h1>

        <div class="mt-5">
            <x-car-search :provinces="$provinces" :filters="$filters" />
        </div>

        <p class="mt-6 text-neutral-600">
            @if ($cars->total() === 0)
                Ningún coche libre con esos datos.
            @else
                {{ trans_choice(':count coche|:count coches', $cars->total(), ['count' => $cars->total()]) }}
                @if ($filters['from'] ?? null)
                    libres {{ \App\Support\DateRange::forHumans($filters['from'], $filters['to']) }}
                @endif
                @if ($filters['province'] ?? null)
                    en {{ $provinces->firstWhere('code', $filters['province'])->name }}
                @endif
            @endif
        </p>

        @if ($cars->isEmpty())
            <div class="card mt-6 p-10 text-center">
                <p class="font-semibold">Aquí no hay nada.</p>
                <p class="mt-1.5 text-sm text-neutral-600">
                    Prueba con otras fechas, o con toda España.
                </p>
            </div>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($cars as $car)
                    <x-car-card :car="$car" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $cars->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
