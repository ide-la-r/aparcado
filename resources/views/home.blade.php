<x-layouts.app>
    <x-dark-section>
        <div class="mx-auto max-w-6xl px-4 pt-16 pb-20 sm:px-6 sm:pt-24 sm:pb-28">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-white/90 ring-1 ring-white/15 ring-inset">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-400 opacity-75"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                </span>
                Alquiler entre particulares
            </p>

            <h1 class="mt-6 max-w-3xl text-5xl leading-[1.05] font-bold text-balance sm:text-7xl">
                El coche que tienes parado en la puerta
                <span class="bg-gradient-to-r from-accent-200 via-accent-400 to-accent-500 bg-clip-text text-transparent">
                    puede estar trabajando</span>.
            </h1>

            <p class="mt-7 max-w-xl text-lg text-white/60">
                Aparcado junta a quien tiene un coche quieto con quien lo necesita un par de
                días. Sin oficinas, sin colas y hablando con el dueño desde el primer mensaje.
            </p>

            <div class="mt-10">
                <x-car-search :provinces="$provinces" />
            </div>

            <dl class="mt-12 grid max-w-2xl grid-cols-3 gap-6 border-t border-white/10 pt-8">
                @foreach ([
                    [$stats['cars'], 'coches publicados'],
                    [$stats['provinces'], 'provincias con coche'],
                    ['0 €', 'por publicar el tuyo'],
                ] as [$dato, $texto])
                    <div>
                        <dt class="font-display text-3xl font-bold sm:text-4xl">{{ $dato }}</dt>
                        <dd class="mt-1 text-sm text-white/50">{{ $texto }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </x-dark-section>

    @if ($latest->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
            <div class="flex flex-wrap items-end justify-between gap-4" data-reveal="0">
                <div>
                    <h2 class="text-2xl font-bold sm:text-3xl">Recién publicados</h2>
                    <p class="mt-2 text-neutral-600">Lo último que ha aparcado alguien aquí.</p>
                </div>

                <a href="{{ route('cars.index') }}" class="btn btn-secondary">Ver todos</a>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $index => $car)
                    <div data-reveal="{{ $index * 90 }}">
                        <x-car-card :car="$car" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section id="como-funciona" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">
        <div data-reveal="0">
            <h2 class="text-2xl font-bold sm:text-3xl">Cómo funciona</h2>
            <p class="mt-2 max-w-xl text-neutral-600">Tres pasos, y el del medio es el que importa.</p>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['Busca', 'Dices tu provincia y los días que lo necesitas. Sólo salen los coches libres en esas fechas.', 'M10.5 3a7.5 7.5 0 1 0 4.55 13.45l4.25 4.25 1.4-1.4-4.25-4.25A7.5 7.5 0 0 0 10.5 3Zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Z'],
                ['Pregunta', 'Hablas con el dueño por el chat: si cabe la silla del niño, dónde se recoge, lo que sea.', 'M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9l-5 4V6a2 2 0 0 1 2-2Z'],
                ['Reserva', 'Pagas y las fechas quedan tuyas. El precio es el del día en que reservaste.', 'M5 4h14a2 2 0 0 1 2 2v3H3V6a2 2 0 0 1 2-2Zm-2 7h18v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7Zm4 4v2h5v-2H7Z'],
            ] as $paso => [$titulo, $texto, $icono])
                <div class="card card-hover group p-6" data-reveal="{{ $paso * 110 }}">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-[0_1px_0_0_rgba(255,255,255,0.3)_inset,0_10px_22px_-8px_var(--color-brand-700)] transition duration-300 group-hover:scale-110 group-hover:-rotate-6">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="{{ $icono }}" />
                        </svg>
                    </span>

                    <h3 class="mt-5 flex items-baseline gap-2 text-lg font-semibold">
                        <span class="font-display text-sm font-bold text-brand-600">0{{ $paso + 1 }}</span>
                        {{ $titulo }}
                    </h3>
                    <p class="mt-2 text-sm text-neutral-600">{{ $texto }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-24 sm:px-6">
        <div class="card overflow-hidden" data-reveal="0">
            <div class="grid gap-8 p-6 sm:grid-cols-2 sm:p-10">
                <div class="flex flex-col justify-center">
                    <h2 class="text-2xl font-bold sm:text-3xl">¿Tienes un coche parado?</h2>
                    <p class="mt-3 text-neutral-600">
                        Publícalo gratis y sin exclusividad. Y si quieres que se vea antes que los
                        demás en tu provincia, hay dos planes.
                    </p>

                    <a href="{{ route('register') }}" class="btn btn-primary btn-shine mt-6 self-start">
                        Publicar mi coche
                    </a>
                </div>

                <ul class="space-y-3">
                    @foreach (config('aparcado.plans') as $slug => $plan)
                        <li @class([
                            'flex items-baseline justify-between gap-4 rounded-xl px-4 py-3.5 transition',
                            'bg-gradient-to-r from-accent-50 to-accent-100/60 ring-1 ring-accent-200 hover:ring-accent-300' => $slug === 'premium',
                            'bg-neutral-50 ring-1 ring-neutral-100 hover:ring-neutral-200' => $slug !== 'premium',
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
