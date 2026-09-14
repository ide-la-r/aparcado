<x-layouts.app title="Créditos de las fotos">
    <x-page-header title="Créditos de las fotos"
                   lead="De dónde salen las fotos de los coches de ejemplo y quién las hizo." />

    <x-prose>
        <p>
            Los coches que salen de ejemplo no existen, pero las fotos sí: son de los
            modelos de verdad y están sacadas en la calle, que es como se ven los anuncios
            de verdad. Vienen de <a href="https://commons.wikimedia.org">Wikimedia
            Commons</a>, y casi todas llevan una licencia <strong>Creative Commons
            BY-SA</strong>, que permite usarlas <em>con la condición de citar a quien las
            hizo</em>. Esta página es esa cita.
        </p>

        <p>
            Las fotos que sube la gente son suyas y no aparecen aquí.
        </p>
    </x-prose>

    <div class="mx-auto max-w-3xl px-4 pb-16 sm:px-6">
        <ul class="card divide-y divide-neutral-100">
            @foreach (config('demo_photos') as $numero => $foto)
                <li class="flex items-center gap-4 p-3">
                    <img src="{{ asset("demo/coche-{$numero}.jpg") }}"
                         alt=""
                         loading="lazy"
                         class="h-16 w-24 shrink-0 rounded-lg object-cover ring-1 ring-neutral-900/5"
                         onerror="this.hidden = true">

                    <div class="min-w-0">
                        <p class="truncate font-semibold text-neutral-900">{{ $foto['model'] }}</p>
                        <p class="mt-0.5 text-sm text-neutral-600">
                            {{ $foto['author'] }} · {{ $foto['licence'] }}
                        </p>
                        <a href="{{ $foto['page'] }}"
                           rel="noopener nofollow"
                           class="mt-0.5 inline-block truncate text-sm font-medium text-brand-700 hover:underline">
                            Ver el original en Commons
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</x-layouts.app>
