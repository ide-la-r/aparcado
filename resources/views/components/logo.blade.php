@props(['class' => 'h-9 w-9'])

{{-- La señal de aparcamiento de la que sale el nombre, dibujada en lugar de
     escrita: una P con su contador dentro de una placa de esquinas redondeadas.
     Una letra metida en un div se lee como un marcador de posición; esto se lee
     como una marca, y aguanta a dieciséis píxeles. --}}
<svg {{ $attributes->merge(['class' => "shrink-0 drop-shadow-[0_6px_16px_rgba(28,63,237,0.35)] {$class}"]) }}
     viewBox="0 0 40 40" fill="none" role="img" aria-label="Aparcado">
    <defs>
        <linearGradient id="logo-placa" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#5885fd" />
            <stop offset="55%" stop-color="#1c3fed" />
            <stop offset="100%" stop-color="#152dda" />
        </linearGradient>

        {{-- El filo claro de arriba: la luz cayendo sobre algo con volumen. --}}
        <linearGradient id="logo-filo" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.45" />
            <stop offset="45%" stop-color="#ffffff" stop-opacity="0" />
        </linearGradient>
    </defs>

    <rect width="40" height="40" rx="11" fill="url(#logo-placa)" />
    <rect width="40" height="40" rx="11" fill="url(#logo-filo)" />
    <rect x="0.6" y="0.6" width="38.8" height="38.8" rx="10.4"
          stroke="#ffffff" stroke-opacity="0.22" stroke-width="1.2" />

    <path fill="#ffffff" fill-rule="evenodd"
          d="M12.6 9.4h9.7c4.7 0 7.9 3 7.9 7.5s-3.2 7.6-7.9 7.6h-4.9v6.1h-4.8V9.4Zm4.8 4.3v6.5h4.2c2.1 0 3.4-1.3 3.4-3.3s-1.3-3.2-3.4-3.2h-4.2Z" />
</svg>
