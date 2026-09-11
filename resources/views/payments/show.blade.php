<x-layouts.app title="Pagar la reserva">
    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6">
        <a href="{{ route('bookings.index') }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-900">
            ← Volver a mis reservas
        </a>

        <h1 class="mt-4 text-2xl font-bold tracking-tight sm:text-3xl">Pagar la reserva</h1>

        <div class="card mt-6 p-5">
            <div class="flex items-start gap-4">
                <x-car-photo :car="$booking->car" class="aspect-[4/3] w-28 shrink-0 rounded-xl" />

                <div>
                    <h2 class="font-semibold">{{ $booking->car->title() }}</h2>
                    <p class="mt-0.5 text-sm text-neutral-500">{{ $booking->car->place() }}</p>
                    <p class="mt-2 text-sm text-neutral-600">{{ $booking->datesForHumans() }}</p>
                </div>
            </div>

            <dl class="mt-5 space-y-1.5 border-t border-neutral-200 pt-5 text-sm">
                <div class="flex justify-between text-neutral-600">
                    <dt>{{ $booking->breakdownForHumans() }}</dt>
                    <dd>{{ $booking->totalForHumans() }}</dd>
                </div>
                <div class="flex justify-between text-base font-semibold">
                    <dt>Total</dt>
                    <dd>{{ $booking->totalForHumans() }}</dd>
                </div>
            </dl>

            <p class="mt-4 text-xs text-neutral-500">
                El importe lo calcula el servidor con el precio que tenía el coche el día en
                que reservaste. No viaja por la dirección del navegador ni se puede cambiar
                desde aquí.
            </p>
        </div>

        @if ($booking->status === \App\Enums\BookingStatus::Confirmed)
            <div class="card mt-4 bg-emerald-50/60 p-5 ring-emerald-200">
                <p class="font-semibold text-emerald-800">Esta reserva ya está pagada.</p>
                <a href="{{ route('bookings.index') }}" class="btn btn-secondary mt-3">Ver mis reservas</a>
            </div>
        @elseif (! $configured)
            <div class="card mt-4 bg-amber-50/60 p-5 ring-amber-200">
                <p class="font-semibold text-amber-900">Los pagos no están configurados.</p>
                <p class="mt-1.5 text-sm text-amber-900/80">
                    Falta poner <code>PAYPAL_CLIENT_ID</code> y <code>PAYPAL_SECRET</code> en el
                    entorno. Se sacan en developer.paypal.com, en «Apps &amp; Credentials», con el
                    conmutador en «Sandbox».
                </p>
            </div>
        @else
            @if ($sandbox)
                <p class="mt-4 text-center text-xs font-medium text-amber-700">
                    Modo de pruebas: no se mueve dinero de verdad.
                </p>
            @endif

            <div id="paypal" class="mt-4"></div>

            <p id="error" class="mt-3 hidden text-sm text-red-600"></p>

            <script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&currency={{ config('services.paypal.currency', 'EUR') }}&locale=es_ES"></script>

            <script>
                const rutaOrden = @js(route('payments.order', $booking));
                const rutaCobro = @js(route('payments.capture', $booking));
                const csrf = @js(csrf_token());
                const error = document.getElementById('error');

                function avisar(texto) {
                    error.textContent = texto;
                    error.hidden = false;
                }

                paypal.Buttons({
                    style: { color: 'blue', shape: 'pill', height: 45, label: 'pay' },

                    // La orden la crea nuestro servidor. Aquí no hay ningún importe:
                    // en el TFG el botón llamaba a `actions.order.create()` con el
                    // precio que hubiera en la página.
                    createOrder: async () => {
                        const respuesta = await fetch(rutaOrden, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        });

                        if (! respuesta.ok) {
                            avisar('No hemos podido abrir el pago. Inténtalo en un rato.');
                            throw new Error('createOrder');
                        }

                        return (await respuesta.json()).id;
                    },

                    // Y lo cobra nuestro servidor, que es quien comprueba el importe
                    // antes de dar la reserva por buena.
                    onApprove: async (data) => {
                        const respuesta = await fetch(rutaCobro, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ order_id: data.orderID }),
                        });

                        const cuerpo = await respuesta.json().catch(() => ({}));

                        if (respuesta.ok) {
                            window.location.href = cuerpo.redirect;

                            return;
                        }

                        avisar(cuerpo.message ?? 'Algo ha ido mal con el pago.');
                    },

                    onError: () => avisar('PayPal ha dado un error. No se te ha cobrado nada.'),
                }).render('#paypal');
            </script>
        @endif
    </div>
</x-layouts.app>
