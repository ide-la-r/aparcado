<x-layouts.app title="Coches">
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Coches disponibles</h1>
                <p class="mt-1.5 text-neutral-600">
                    {{ trans_choice(':count coche publicado|:count coches publicados', $cars->total(), ['count' => $cars->total()]) }}
                </p>
            </div>
        </header>

        @if ($cars->isEmpty())
            <div class="card mt-8 p-10 text-center">
                <p class="font-semibold">Todavía no hay coches publicados.</p>
                <p class="mt-1.5 text-sm text-neutral-600">Vuelve en un rato.</p>
            </div>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
