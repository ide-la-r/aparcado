@props(['car', 'class' => 'aspect-[4/3]'])

{{-- Detrás de la foto hay siempre un fondo con la silueta de un coche y su marca.
     Así, mientras la imagen carga —o si el fichero se ha perdido, que en cuatro
     años pasa— la tarjeta sigue diciendo qué coche es en lugar de enseñar un icono
     roto. Y como casi ningún anuncio tiene foto todavía, esto es lo que más se ve:
     merece la pena que no sea un rectángulo gris. --}}
<div {{ $attributes->merge(['class' => "relative isolate flex items-center justify-center overflow-hidden bg-gradient-to-br from-brand-100 via-brand-50 to-accent-50 {$class}"]) }}>
    {{-- La silueta va centrada y a media anchura, con el recorte del viewBox justo
         al dibujo: a pantalla completa tapaba la tarjeta entera. --}}
    <svg class="absolute top-1/2 left-1/2 w-1/2 -translate-x-1/2 -translate-y-[58%] text-brand-700/10"
         viewBox="8 54 148 58" fill="currentColor" aria-hidden="true">
        <path d="M18 96c-4 0-7-3-7-7v-9c0-5 3-9 8-11l13-4 12-19c3-5 8-8 14-8h50c6 0 11 3 14 8l12 19 13 4c5 2 8 6 8 11v9c0 4-3 7-7 7h-9a14 14 0 0 1-27 0H54a14 14 0 0 1-27 0h-9Z" />
    </svg>

    <span class="relative z-10 mt-[18%] px-4 text-center text-sm font-semibold text-brand-800/70">{{ $car->title() }}</span>

    @if ($photo = $car->coverPhoto())
        <img src="{{ $photo->url() }}"
             alt="{{ $car->title() }}"
             loading="lazy"
             class="absolute inset-0 z-20 h-full w-full object-cover"
             onerror="this.hidden = true">
    @endif
</div>
