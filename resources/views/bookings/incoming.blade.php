<x-layouts.app title="Reservas de mis coches">
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Reservas de mis coches</h1>
        <p class="mt-1.5 text-neutral-600">Lo que te han pedido.</p>

        @if ($bookings->isEmpty())
            <div class="card mt-8 p-10 text-center">
                <p class="font-semibold">Nadie ha pedido tus coches todavía.</p>
                <p class="mt-1.5 text-sm text-neutral-600">
                    Cuantas más fotos y más datos tenga el anuncio, antes llega la primera.
                </p>
            </div>
        @else
            <div class="mt-8 space-y-3">
                @foreach ($bookings as $booking)
                    <x-booking-row :booking="$booking" side="owner" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
