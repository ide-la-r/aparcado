@props(['car', 'class' => 'aspect-[4/3]'])

{{-- Detrás de la foto hay siempre un fondo con la marca y el modelo. Así, mientras
     la imagen carga —o si el fichero se ha perdido, que en cuatro años pasa— la
     tarjeta sigue diciendo qué coche es en lugar de enseñar un icono roto. --}}
<div {{ $attributes->merge(['class' => "relative flex items-center justify-center overflow-hidden bg-brand-50 {$class}"]) }}>
    <span class="px-4 text-center text-sm font-semibold text-brand-700/70">{{ $car->title() }}</span>

    @if ($photo = $car->coverPhoto())
        <img src="{{ Storage::url($photo->path) }}"
             alt="{{ $car->title() }}"
             loading="lazy"
             class="absolute inset-0 h-full w-full object-cover"
             onerror="this.hidden = true">
    @endif
</div>
