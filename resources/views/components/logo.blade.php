@props(['class' => 'h-9 w-9'])

{{-- Una P blanca sobre azul: es literalmente la señal de aparcamiento de la que
     sale el nombre, y se reconoce en 16 píxeles. --}}
<span {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-xl bg-brand-600 font-bold text-white {$class}"]) }}
      aria-hidden="true">
    P
</span>
