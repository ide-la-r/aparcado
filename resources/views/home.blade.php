<x-layouts.app>
    <section class="relative isolate overflow-hidden bg-ink ink-glow text-white">
        {{-- Una rejilla muy tenue para que el fondo oscuro tenga textura y no sea
             un rectángulo negro. --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
             style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 56px 56px;"
             aria-hidden="true"></div>

        <div class="relative mx-auto max-w-6xl px-4 pt-16 pb-20 sm:px-6 sm:pt-24 sm:pb-28">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-white/90 ring-1 ring-white/15 ring-inset">
                <span class="h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                Alquiler entre particulares
            </p>

            <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-tight text-balance sm:text-6xl">
                El coche que tienes parado en la puerta
                <span class="bg-gradient-to-r from-accent-300 to-accent-500 bg-clip-text text-transparent">
                    puede estar trabajando</span>.
            </h1>

            <p class="mt-6 max-w-xl text-lg text-white/70">
                Aparcado junta a quien tiene un coche quieto con quien lo necesita un par de
                días. Sin oficinas, sin colas y hablando con el dueño desde el primer mensaje.
            </p>

            <div class="mt-10">
                <x-car-search :provinces="$provinces" />
            </div>

            <p class="mt-6 text-sm text-white/50">
                ¿Es la primera vez?
                <a href="#como-funciona" class="font-medium text-white underline decoration-white/30 underline-offset-4 hover:decoration-white">
                    Mira cómo funciona</a>.
            </p>
        </div>
    </section>

    <section id="como-funciona" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
        <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Cómo funciona</h2>
        <p class="mt-2 max-w-xl text-neutral-600">Tres pasos, y el del medio es el que importa.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['Busca', 'Dices tu provincia y los días que lo necesitas. Sólo salen los coches libres en esas fechas.', 'M10.5 3a7.5 7.5 0 1 0 4.55 13.45l4.25 4.25 1.4-1.4-4.25-4.25A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z'],
                ['Pregunta', 'Hablas con el dueño por el chat: si cabe la silla del niño, dónde se recoge, lo que sea.', 'M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9l-5 4V6a2 2 0 0 1 2-2Z'],
                ['Reserva', 'Pagas y las fechas quedan tuyas. El precio es el del día en que reservaste.', 'M5 4h14a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2Zm-2 7h18v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7Zm4 4v2h5v-2H7Z'],
            ] as $paso => [$titulo, $texto, $icono])
                <div class="card card-hover p-6">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-lg shadow-brand-600/25">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="{{ $icono }}" />
                        </svg>
                    </span>

                    <h3 class="mt-4 flex items-baseline gap-2 font-semibold">
                        <span class="text-xs font-bold text-brand-600">0{{ $paso + 1 }}</span>
                        {{ $titulo }}
                    </h3>
                    <p class="mt-1.5 text-sm text-neutral-600">{{ $texto }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
        <div class="card overflow-hidden">
            <div class="grid gap-8 p-6 sm:grid-cols-2 sm:p-10">
                <div class="flex flex-col justify-center">
                    <h2 class="text-2xl font-bold tracking-tight">¿Tienes un coche parado?</h2>
                    <p class="mt-3 text-neutral-600">
                        Publícalo gratis y sin exclusividad. Y si quieres que se vea antes que los
                        demás en tu provincia, hay dos planes.
                    </p>

                    <a href="{{ route('register') }}" class="btn btn-primary mt-6 self-start">Publicar mi coche</a>
                </div>

                <ul class="space-y-3">
                    @foreach (config('aparcado.plans') as $slug => $plan)
                        <li @class([
                            'flex items-baseline justify-between gap-4 rounded-xl px-4 py-3.5',
                            'bg-gradient-to-r from-accent-50 to-accent-100/60 ring-1 ring-accent-200' => $slug === 'premium',
                            'bg-neutral-50 ring-1 ring-neutral-100' => $slug !== 'premium',
                        ])>
                            <div>
                                <p class="flex flex-wrap items-center gap-2 font-semibold">
                                    {{ $plan['name'] }}
                                    @if ($slug === 'premium')
                                        <span class="chip chip-accent">El primero de todos</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-sm text-neutral-600">{{ $plan['blurb'] }}</p>
                            </div>

                            <p class="shrink-0 text-right text-sm">
                                <span class="price">{{ \App\Support\Money::format($plan['price_cents']) }}</span>
                                <span class="block text-xs text-neutral-500">al mes</span>
                            </p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
</x-layouts.app>
