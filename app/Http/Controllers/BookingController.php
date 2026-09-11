<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Car;
use App\Services\Bookings\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    /** Las que he pedido yo. */
    public function index(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['car.province', 'car.photos', 'car.owner'])
            ->orderByDesc('starts_on')
            ->get();

        return view('bookings.index', ['bookings' => $bookings]);
    }

    /** Las que me han pedido a mí. */
    public function incoming(Request $request): View
    {
        $bookings = Booking::query()
            ->whereHas('car', fn ($car) => $car->where('owner_id', $request->user()->id))
            ->with(['car', 'renter'])
            ->orderByDesc('starts_on')
            ->get();

        return view('bookings.incoming', ['bookings' => $bookings]);
    }

    public function store(StoreBookingRequest $request, Car $car): RedirectResponse
    {
        // El coche de uno mismo no se alquila, y esconderlo del catálogo tampoco
        // debería dejar reservarlo por el enlace directo.
        abort_if($car->owner_id === $request->user()->id, 403);
        abort_unless($car->published, 404);

        $booking = $this->bookings->request(
            $request->user(),
            $car,
            $request->validated('from'),
            $request->validated('to'),
        );

        return redirect()
            ->route('bookings.index')
            ->with('status', "Pedido {$car->title()} para ".$booking->datesForHumans().'. Falta pagarlo.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        abort_unless($booking->isCancellable(), 403);

        $this->bookings->cancel($booking);

        return back()->with('status', 'Reserva cancelada.');
    }
}
