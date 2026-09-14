<x-layouts.app :title="$car->title()">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <a href="{{ route('cars.index', array_filter(['from' => $from, 'to' => $to])) }}"
           class="text-sm font-medium text-neutral-500 hover:text-neutral-900">
            ← Volver a los coches
        </a>

        {{-- El nombre va delante de las fotos: así la pestaña del navegador, el
             titular y lo primero que se lee dicen lo mismo, y quien llega de una
             búsqueda sabe dónde ha caído sin esperar a que cargue una imagen. --}}
        <header class="mt-4">
            <h1 class="text-3xl font-bold text-balance sm:text-4xl">{{ $car->title() }}</h1>
            <p class="mt-1.5 flex items-center gap-1.5 text-neutral-600">
                <svg class="h-4 w-4 shrink-0 text-neutral-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z" />
                </svg>
                {{ $car->place() }}
            </p>
        </header>

        {{-- El mosaico: la foto grande manda y las demás la acompañan a la derecha.
             Con una sola foto no hay mosaico que valga, así que ocupa todo el ancho
             en lugar de dejar medio hueco vacío al lado. --}}
        @php $extra = $car->photos->skip(1)->take(2); @endphp

        <div @class([
            'mt-6 grid gap-2 overflow-hidden rounded-2xl',
            'sm:grid-cols-[2fr_1fr]' => $extra->isNotEmpty(),
        ])>
            {{-- Con una sola foto se manda la altura a mano: un 16/9 a todo el ancho
                 mide más de seiscientos píxeles y se come la pantalla entera antes
                 de que se lea un solo dato del coche. --}}
            <x-car-photo :car="$car" :class="$extra->isNotEmpty() ? 'aspect-[4/3] sm:aspect-auto sm:h-full sm:min-h-[22rem]' : 'h-64 sm:h-[24rem]'" />

            @if ($extra->isNotEmpty())
                <div class="grid gap-2 {{ $extra->count() > 1 ? 'grid-cols-2 sm:grid-cols-1' : '' }}">
                    @foreach ($extra as $photo)
                        <div class="aspect-[4/3] overflow-hidden bg-gradient-to-br from-brand-100 via-brand-50 to-accent-50 sm:aspect-auto sm:h-full">
                            <img src="{{ $photo->url() }}" alt=""
                                 loading="lazy" class="h-full w-full object-cover"
                                 onerror="this.hidden = true">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1.6fr_1fr]">
            <div>
                {{-- La ficha técnica, en una rejilla con sus rayas: nueve pares de
                     dato y etiqueta sueltos en el blanco se leen como una lista de la
                     compra. --}}
                {{-- Las rayas son el hueco de un píxel entre celdas, con el fondo de
                     la rejilla asomando: así salen solas en cualquier número de
                     columnas y no hay que ir contando hijos por cada tamaño. --}}
                <dl class="card grid grid-cols-2 gap-px overflow-hidden bg-neutral-100 sm:grid-cols-3">
                    @foreach ([
                        'Combustible' => $car->fuel,
                        'Cambio' => $car->transmission,
                        'Carrocería' => $car->body_type,
                        'Plazas' => $car->seats,
                        'Puertas' => $car->doors,
                        'Potencia' => $car->power_hp.' CV',
                        'Matriculado' => $car->registration_year,
                        'Kilómetros' => number_format($car->kilometres, 0, ',', '.').' km',
                        'Color' => $car->colour,
                    ] as $term => $value)
                        <div class="bg-white px-4 py-3.5">
                            <dt class="text-[0.7rem] font-semibold tracking-wide text-neutral-400 uppercase">{{ $term }}</dt>
                            <dd class="mt-1 font-medium text-neutral-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($car->description)
                    <div class="mt-8">
                        <h2 class="text-lg font-semibold">Lo que cuenta el dueño</h2>
                        <p class="mt-2 whitespace-pre-line text-neutral-700">{{ $car->description }}</p>
                    </div>
                @endif

                @if ($car->features->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="font-semibold">Lo que lleva</h2>

                        <div class="mt-3 space-y-5">
                            @foreach ($car->features->groupBy('group') as $group => $features)
                                <div>
                                    <h3 class="text-xs font-medium tracking-wide text-neutral-500 uppercase">
                                        {{ $groups[$group] ?? $group }}
                                    </h3>
                                    <ul class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($features as $feature)
                                            <li class="chip">{{ $feature->name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($car->latitude && $car->longitude)
                    @php
                        // Redondeado a dos decimales antes de salir de aquí: así ni el mapa
                        // sabe dónde está el coche exactamente, sólo la zona. El punto de
                        // recogida lo da el dueño cuando hay reserva.
                        $lat = round((float) $car->latitude, 2);
                        $lon = round((float) $car->longitude, 2);
                        $bbox = implode(',', [$lon - 0.014, $lat - 0.009, $lon + 0.014, $lat + 0.009]);
                    @endphp

                    <div class="mt-8">
                        <h2 class="font-semibold">Dónde está</h2>
                        <div class="mt-3 overflow-hidden rounded-2xl ring-1 ring-neutral-200">
                            <iframe title="Zona donde está el coche"
                                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $bbox }}&layer=mapnik"
                                    loading="lazy"
                                    class="h-64 w-full border-0"></iframe>
                        </div>
                        <p class="mt-2 text-xs text-neutral-500">
                            Zona aproximada. El punto de recogida lo acordáis por el chat.
                        </p>
                    </div>
                @endif
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 lg:h-fit">
                <div class="card p-5">
                    <p>
                        <span class="text-2xl font-bold">{{ $car->priceForHumans() }}</span>
                        <span class="text-sm text-neutral-500">al día</span>
                    </p>

                    <form method="GET" action="{{ route('cars.show', $car) }}"
                          x-data="{ from: @js($from) }"
                          class="mt-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="label" for="from">Entrada</label>
                                <input id="from" type="date" name="from" class="field"
                                       x-model="from" min="{{ now()->toDateString() }}" value="{{ $from }}">
                            </div>
                            <div>
                                <label class="label" for="to">Salida</label>
                                <input id="to" type="date" name="to" class="field"
                                       :min="from || '{{ now()->toDateString() }}'" value="{{ $to }}">
                            </div>
                        </div>

                        @error('from')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        @error('to')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                        <button type="submit" class="btn btn-secondary w-full">Ver disponibilidad</button>
                    </form>

                    @if ($quote)
                        <div class="mt-5 border-t border-neutral-200 pt-5">
                            @if ($free)
                                <p class="text-sm font-semibold text-emerald-700">
                                    Libre {{ \App\Support\DateRange::forHumans($from, $to) }}
                                </p>

                                <dl class="mt-3 space-y-1.5 text-sm">
                                    <div class="flex justify-between text-neutral-600">
                                        <dt>{{ $quote->breakdownForHumans() }}</dt>
                                        <dd>{{ $quote->totalForHumans() }}</dd>
                                    </div>
                                    <div class="flex justify-between border-t border-neutral-200 pt-1.5 font-semibold">
                                        <dt>Total</dt>
                                        <dd>{{ $quote->totalForHumans() }}</dd>
                                    </div>
                                </dl>

                                @guest
                                    <a href="{{ route('login') }}" class="btn btn-primary mt-4 w-full">Entrar para reservar</a>
                                @elseif (auth()->id() === $car->owner_id)
                                    <p class="mt-4 text-center text-sm text-neutral-500">Este coche es tuyo.</p>
                                    <a href="{{ route('my-cars.edit', $car) }}" class="btn btn-secondary mt-2 w-full">Editarlo</a>
                                @elseif (! auth()->user()->isVerified())
                                    <a href="{{ route('profile.show') }}" class="btn btn-secondary mt-4 w-full">
                                        Verifica tu cuenta para reservar
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('bookings.store', $car) }}" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="from" value="{{ $from }}">
                                        <input type="hidden" name="to" value="{{ $to }}">
                                        <button type="submit" class="btn btn-primary w-full">Reservar</button>
                                    </form>
                                    <p class="mt-2 text-center text-xs text-neutral-500">
                                        Aquí no se paga nada todavía: las fechas no son tuyas hasta pagarlas.
                                    </p>
                                @endguest
                            @else
                                <p class="text-sm font-semibold text-neutral-900">Este coche está cogido esos días.</p>
                                <p class="mt-1 text-sm text-neutral-600">
                                    Prueba otras fechas, o mira el resto del catálogo.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="card p-5">
                    <h2 class="text-xs font-medium tracking-wide text-neutral-500 uppercase">El dueño</h2>

                    <div class="mt-3 flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-200 font-semibold text-neutral-600">
                            {{ mb_substr($car->owner->name, 0, 1) }}
                        </span>
                        <div>
                            <p class="font-semibold">{{ $car->owner->name }}</p>
                            <p class="text-sm text-neutral-500">
                                @if ($car->owner->isVerified())
                                    Identidad verificada
                                @else
                                    Sin verificar
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Hablar con el dueño no pide tener los papeles comprobados:
                         preguntar es gratis y es lo que hace que alguien reserve. --}}
                    @auth
                        @if (auth()->id() !== $car->owner_id)
                            <form method="POST" action="{{ route('messages.start', $car) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="btn btn-secondary w-full">Escribir al dueño</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary mt-4 w-full">Entrar para escribirle</a>
                    @endauth
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
