<x-layouts.app title="Mis reservas">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Mis reservas</h1>
        <p class="mt-1.5 text-neutral-600">Los coches que has pedido.</p>

        @if ($bookings->isEmpty())
            <div class="card mt-8 p-10 text-center">
                <p class="font-semibold">Todavía no has reservado nada.</p>
                <a href="{{ route('cars.index') }}" class="btn btn-primary mt-5">Buscar un coche</a>
            </div>
        @else
            <div class="mt-8 space-y-3">
                @foreach ($bookings as $booking)
                    <x-booking-row :booking="$booking" side="renter" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
