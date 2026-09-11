<x-layouts.app :title="$car->title()">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $car->title() }}</h1>
                <p class="mt-1.5 text-neutral-600">{{ $car->plate }}</p>
            </div>

            <a href="{{ route('cars.show', $car) }}" class="btn btn-secondary">Ver la ficha</a>
        </div>

        <x-car-form :car="$car" :action="route('my-cars.update', $car)" method="PATCH"
                    :provinces="$provinces" :features="$features" :groups="$groups">
            Guardar
        </x-car-form>

        <form method="POST" action="{{ route('my-cars.destroy', $car) }}" class="mt-10"
              onsubmit="return confirm('¿Retirar este coche? Las reservas que ya haya se quedan.')">
            @csrf
            @method('DELETE')

            <h2 class="font-semibold text-neutral-900">Retirar el coche</h2>
            <p class="mt-1.5 text-sm text-neutral-600">
                Desaparece del catálogo para siempre. Las reservas que ya hubo se quedan en el
                historial, tuyo y de quien lo alquiló.
            </p>
            <button type="submit" class="btn btn-secondary mt-3 text-red-700">Retirar</button>
        </form>
    </div>
</x-layouts.app>
