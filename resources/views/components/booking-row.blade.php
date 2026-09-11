@props(['booking', 'side' => 'renter'])

@php
    $car = $booking->car;
    $status = $booking->status;
@endphp

<div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
    <x-car-photo :car="$car" class="aspect-[4/3] w-full shrink-0 rounded-xl sm:w-32" />

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="font-semibold">{{ $car->title() }}</h2>

            <span @class([
                'chip',
                'bg-amber-100 text-amber-900' => $status === \App\Enums\BookingStatus::Pending,
                'bg-emerald-100 text-emerald-800' => $status === \App\Enums\BookingStatus::Confirmed,
                'bg-neutral-200 text-neutral-700' => $status === \App\Enums\BookingStatus::Cancelled,
            ])>{{ $status->label() }}</span>
        </div>

        <p class="mt-0.5 text-sm text-neutral-600">{{ $booking->datesForHumans() }}</p>

        <p class="mt-2 text-sm text-neutral-600">
            {{ $booking->breakdownForHumans() }} ·
            <span class="font-semibold text-neutral-900">{{ $booking->totalForHumans() }}</span>
        </p>

        <p class="mt-1 text-sm text-neutral-500">
            @if ($side === 'renter')
                El coche es de {{ $car->owner->name }}
            @else
                Lo ha pedido {{ $booking->renter->name }}
            @endif
        </p>
    </div>

    <div class="flex shrink-0 flex-wrap gap-2">
        <a href="{{ route('cars.show', $car) }}" class="btn btn-ghost">Ver el coche</a>

        @if ($status === \App\Enums\BookingStatus::Pending && $side === 'renter')
            <button type="button" class="btn btn-primary" disabled>Pagar</button>
        @endif

        @if ($booking->isCancellable())
            <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                  onsubmit="return confirm('¿Cancelar esta reserva?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-secondary text-red-700">Cancelar</button>
            </form>
        @endif
    </div>
</div>
