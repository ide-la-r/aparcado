<x-layouts.app>
    <x-dark-section>
        <div class="mx-auto max-w-6xl px-4 pt-10 sm:px-6 sm:pt-20">
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-white/90 ring-1 ring-white/15 ring-inset">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-400 opacity-75"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                        </span>
                        Alquiler entre particulares
                    </p>

                    {{-- En el móvil el titular baja a 36 px: a 48 ocupaba la pantalla
                         entera él solo y no quedaba sitio ni para una foto. --}}
                    <h1 class="mt-6 text-4xl leading-[1.05] font-bold text-balance sm:text-6xl sm:leading-[1.03] xl:text-7xl">
                        El coche que tienes parado en la puerta
                        <span class="bg-gradient-to-r from-accent-200 via-accent-400 to-accent-500 bg-clip-text text-transparent">
                            puede estar trabajando</span>.
                    </h1>

                    <p class="mt-6 max-w-md text-white/60 sm:mt-7 sm:text-lg">
                        Aparcado junta a quien tiene un coche quieto con quien lo necesita un par de
                        días. Sin oficinas, sin colas y hablando con el dueño desde el primer mensaje.
                    </p>
                </div>

                {{-- La ilustración de la portada es la aplicación misma: un anuncio
                     de verdad, con su foto y su precio, y un trozo de chat encima.
                     Enseñar lo que hay dice en un vistazo lo que un dibujo no dice en
                     tres párrafos, y promete exactamente lo que se va a encontrar al
                     entrar.

                     Una sola ficha y no un montón: en cuanto se apilan dos, una tapa
                     el nombre de la otra y lo que se lee es un lío. La variedad ya la
                     cuenta la tira de abajo.

                     Y es un enlace de verdad al coche, no un adorno: en el móvil es
                     lo primero que se ve, y lo primero que se ve tiene que llevar a
                     algún sitio. --}}
                @if ($showcase)
                    <div class="relative">
                        {{-- El resplandor de detrás separa la ficha del fondo oscuro
                             sin tener que ponerle un borde. --}}
                        <div class="absolute -inset-10 -z-10 rounded-full bg-brand-500/25 blur-3xl"></div>

                        <div class="animate-bob">
                            {{-- El giro sólo en pantalla grande: en el móvil la ficha
                                 ocupa todo el ancho y una torcida se sale por los
                                 lados. --}}
                            <a href="{{ route('cars.show', $showcase) }}"
                               class="group relative block overflow-hidden rounded-3xl bg-white shadow-2xl shadow-brand-950/60 ring-1 ring-white/10 transition duration-300 lg:rotate-[-3deg] lg:hover:rotate-0">
                                <x-car-photo :car="$showcase" class="aspect-[16/10] transition duration-700 group-hover:scale-[1.05] sm:aspect-[4/3]" />

                                {{-- El rótulo va encima de la foto, no debajo: la ficha
                                     se queda en una sola pieza y el coche ocupa todo. --}}
                                <div class="photo-scrim z-30"></div>

                                <div class="absolute inset-x-0 bottom-0 z-30 flex items-end justify-between gap-3 p-4 sm:p-5">
                                    <div class="min-w-0">
                                        <p class="truncate font-display text-lg font-bold text-white">
                                            {{ $showcase->title() }}
                                        </p>
                                        <p class="mt-0.5 truncate text-sm text-white/70">{{ $showcase->place() }}</p>
                                    </div>

                                    <p class="price-tag shrink-0">
                                        <span class="price text-base">{{ $showcase->priceForHumans() }}</span>
                                        <span class="text-[0.7rem] font-medium text-neutral-500">/día</span>
                                    </p>
                                </div>
                            </a>
                        </div>

                        {{-- Y el chat, que es lo que separa esto de una web de alquiler
                             de toda la vida: aquí se habla con una persona. Es un
                             ejemplo dibujado, así que no se lee en voz alta ni ocupa
                             sitio en el móvil. --}}
                        <div class="animate-bob-slow absolute -top-8 right-0 hidden w-56 lg:block xl:-right-8"
                             aria-hidden="true">
                            <p class="rounded-2xl rounded-br-sm bg-white px-3.5 py-2.5 text-sm text-neutral-700 shadow-xl shadow-brand-950/40">
                                ¿Cabe la silla del niño?
                            </p>
                            <p class="mt-2 ml-8 rounded-2xl rounded-bl-sm bg-accent-400 px-3.5 py-2.5 text-sm font-medium text-accent-950 shadow-xl shadow-brand-950/40">
                                Lleva Isofix, te la dejo puesta
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-12 sm:mt-14">
                <x-car-search :provinces="$provinces" />
            </div>

            <dl class="mt-10 grid max-w-2xl grid-cols-3 gap-6 border-t border-white/10 pt-8">
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

        {{-- La portada no termina en una raya: termina en una tira de coches que
             cruza la pantalla muy despacio. Es lo que hace que se entienda de qué va
             esto antes de leer una sola palabra. --}}
        @if ($strip->count() >= 4)
            <div class="marquee-mask mt-14 overflow-hidden pb-14" aria-hidden="true">
                <div class="animate-marquee flex w-max gap-4">
                    {{-- La lista va dos veces: por eso el desplazamiento del 50 % cae
                         justo en el mismo sitio y el bucle no da tirón. --}}
                    @foreach (range(1, 2) as $vuelta)
                        @foreach ($strip as $car)
                            <img src="{{ $car->coverPhoto()->url() }}"
                                 alt=""
                                 loading="lazy"
                                 decoding="async"
                                 class="h-28 w-44 shrink-0 rounded-xl object-cover opacity-70 ring-1 ring-white/10 sm:h-32 sm:w-52">
                        @endforeach
                    @endforeach
                </div>
            </div>
        @else
            <div class="pb-20"></div>
        @endif
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

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $index => $car)
                    <div data-reveal="{{ $index * 80 }}">
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

    {{-- La despedida en oscuro, haciendo pareja con la portada, y el único botón
         con halo de toda la página: si hubiera dos, ninguno llamaría.

         Va en un panel con las esquinas redondeadas y aire alrededor, no en una
         franja a sangre. Pegada al pie —que también es oscuro— eran ochocientos
         píxeles seguidos de negro para terminar la página, y por bien que se
         empalmaran los dos fondos el corte se notaba. Así el blanco vuelve a
         separarlos, la página alterna claro y oscuro de arriba abajo, y la llamada
         se queda donde tiene que estar: la última. --}}
    {{-- Sin relleno abajo: la separación con el pie ya la pone su propio margen, y
         sumando las dos salían ciento sesenta píxeles de blanco. --}}
    <section class="mx-auto max-w-6xl px-4 pt-2 sm:px-6 sm:pt-4">
    <x-dark-section tone="soft" class="rounded-3xl shadow-2xl shadow-brand-950/20">
        <div class="grid gap-10 p-8 sm:grid-cols-2 sm:p-12">
            <div class="flex flex-col justify-center">
                <h2 class="text-3xl font-bold text-balance sm:text-4xl">¿Tienes un coche parado?</h2>
                <p class="mt-4 max-w-sm text-white/60">
                    Publícalo gratis y sin exclusividad. Y si quieres que se vea antes que los
                    demás en tu provincia, hay dos planes.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-halo">Publicar mi coche</a>
                    <a href="{{ route('pages.help') }}" class="btn btn-on-dark">Cómo va lo de los planes</a>
                </div>
            </div>

            <ul class="space-y-3">
                @foreach (config('aparcado.plans') as $slug => $plan)
                    <li @class([
                        'flex items-baseline justify-between gap-4 rounded-xl px-4 py-4 backdrop-blur transition',
                        'bg-accent-400/10 ring-1 ring-accent-400/35 hover:ring-accent-400/60' => $slug === 'premium',
                        'bg-white/5 ring-1 ring-white/10 hover:ring-white/20' => $slug !== 'premium',
                    ])>
                        <div>
                            <p class="flex flex-wrap items-center gap-2 font-semibold">
                                {{ $plan['name'] }}
                                @if ($slug === 'premium')
                                    <span class="chip bg-accent-400/20 text-accent-200">El primero de todos</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-sm text-white/55">{{ $plan['blurb'] }}</p>
                        </div>

                        <p class="shrink-0 text-right text-sm">
                            <span class="font-display font-bold tracking-tight text-white">
                                {{ \App\Support\Money::format($plan['price_cents']) }}
                            </span>
                            <span class="block text-xs text-white/40">al mes</span>
                        </p>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-dark-section>
    </section>
</x-layouts.app>
