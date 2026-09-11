<x-layouts.app>
    <section class="relative overflow-hidden border-b border-neutral-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
            <div class="max-w-2xl">
                <p class="chip">Alquiler entre particulares</p>

                <h1 class="mt-5 text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl">
                    El coche que tienes parado en la puerta
                    <span class="text-brand-600">puede estar trabajando</span>.
                </h1>

                <p class="mt-5 text-lg text-neutral-600">
                    Aparcado junta a quien tiene un coche quieto con quien lo necesita un par
                    de días. Sin oficinas, sin colas y hablando con el dueño desde el primer
                    mensaje.
                </p>

                <div class="mt-8">
                    <x-car-search :provinces="$provinces" />
                </div>

                <p class="mt-6 text-sm text-neutral-500">
                    ¿Es la primera vez? <a href="#como-funciona" class="font-medium text-brand-700 underline decoration-brand-300 underline-offset-2 hover:decoration-brand-600">Mira cómo funciona</a>.
                </p>
            </div>
        </div>
    </section>

    <section id="como-funciona" class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Cómo funciona</h2>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['Busca', 'Dices tu provincia y los días que lo necesitas. Sólo salen los coches libres en esas fechas.'],
                ['Pregunta', 'Hablas con el dueño por el chat: si cabe la silla del niño, dónde se recoge, lo que sea.'],
                ['Reserva', 'Pagas y las fechas quedan tuyas. El precio es el del día en que reservaste.'],
            ] as $paso => $bloque)
                <div class="card p-6">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 font-bold text-white">
                        {{ $paso + 1 }}
                    </span>
                    <h3 class="mt-4 font-semibold">{{ $bloque[0] }}</h3>
                    <p class="mt-1.5 text-sm text-neutral-600">{{ $bloque[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6">
        <div class="card overflow-hidden">
            <div class="grid gap-8 p-6 sm:grid-cols-2 sm:p-10">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">¿Tienes un coche parado?</h2>
                    <p class="mt-3 text-neutral-600">
                        Publícalo gratis. Y si quieres que se vea antes que los demás en tu
                        provincia, hay dos planes.
                    </p>
                </div>

                <ul class="space-y-3">
                    @foreach (config('aparcado.plans') as $plan)
                        <li class="flex items-baseline justify-between gap-4 rounded-xl bg-neutral-50 px-4 py-3">
                            <div>
                                <p class="font-semibold">{{ $plan['name'] }}</p>
                                <p class="text-sm text-neutral-600">{{ $plan['blurb'] }}</p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold text-neutral-900">
                                {{ \App\Support\Money::format($plan['price_cents']) }}<span class="font-normal text-neutral-500">/mes</span>
                            </p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
</x-layouts.app>
