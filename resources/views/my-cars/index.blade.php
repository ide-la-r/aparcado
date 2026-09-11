<x-layouts.app title="Mis coches">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Mis coches</h1>
            <a href="{{ route('my-cars.create') }}" class="btn btn-primary">Publicar un coche</a>
        </div>

        @if ($cars->isEmpty())
            <div class="card mt-8 p-10 text-center">
                <p class="font-semibold">Todavía no tienes ninguno publicado.</p>
                <p class="mt-1.5 text-sm text-neutral-600">
                    Si tienes un coche parado, aquí es donde se cuenta.
                </p>
                <a href="{{ route('my-cars.create') }}" class="btn btn-primary mt-5">Publicar un coche</a>
            </div>
        @else
            <div class="mt-8 space-y-3">
                @foreach ($cars as $car)
                    <div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <x-car-photo :car="$car" class="aspect-[4/3] w-full shrink-0 rounded-xl sm:w-40" />

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold">{{ $car->title() }}</h2>

                                @if ($car->published)
                                    <span class="chip bg-emerald-100 text-emerald-800">En el catálogo</span>
                                @else
                                    <span class="chip">Escondido</span>
                                @endif
                            </div>

                            <p class="mt-0.5 text-sm text-neutral-500">
                                {{ $car->plate }} · {{ $car->place() }}
                            </p>

                            <p class="mt-2 text-sm text-neutral-600">
                                {{ $car->priceForHumans() }} al día ·
                                {{ trans_choice(':count reserva|:count reservas', $car->bookings_count, ['count' => $car->bookings_count]) }}
                            </p>
                        </div>

                        <div class="flex shrink-0 gap-2">
                            <a href="{{ route('cars.show', $car) }}" class="btn btn-ghost">Ver</a>
                            <a href="{{ route('my-cars.edit', $car) }}" class="btn btn-secondary">Editar</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
