@props(['class' => 'h-9 w-9'])

{{-- Una P blanca sobre azul: es literalmente la señal de aparcamiento de la que
     sale el nombre, y se reconoce en dieciséis píxeles. Con degradado y un poco de
     sombra, que un cuadrado plano parece un marcador de posición. --}}
<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-700 font-bold text-white shadow-lg shadow-brand-600/30 {$class}"]) }}
      aria-hidden="true">
    P
</span>
