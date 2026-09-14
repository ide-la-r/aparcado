@props(['class' => 'h-9 w-9'])

{{-- Un coche visto desde arriba, metido entre las dos rayas de una plaza: el
     nombre dibujado.

     Lo que hace que se lea como un coche y no como un rectángulo son tres cosas:
     el morro estrecha, el parabrisas y la luneta son trapecios, y los retrovisores
     sobresalen por los lados. Sin los retrovisores esto es una puerta. --}}
<svg {{ $attributes->merge(['class' => "shrink-0 {$class}"]) }} viewBox="0 0 40 40" fill="none"
     role="img" aria-label="Aparcado">
    <defs>
        <linearGradient id="aparcado-placa" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#5885fd" />
            <stop offset="55%" stop-color="#1c3fed" />
            <stop offset="100%" stop-color="#152dda" />
        </linearGradient>

        {{-- El filo claro de arriba: la luz cayendo sobre algo con volumen. --}}
        <linearGradient id="aparcado-filo" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.4" />
            <stop offset="45%" stop-color="#ffffff" stop-opacity="0" />
        </linearGradient>
    </defs>

    <rect width="40" height="40" rx="11" fill="url(#aparcado-placa)" />
    <rect width="40" height="40" rx="11" fill="url(#aparcado-filo)" />

    {{-- Las dos rayas de la plaza, sólo los lados. --}}
    <rect x="6.2" y="7.4" width="2.6" height="25.2" rx="1.3" fill="#ffffff" opacity="0.5" />
    <rect x="31.2" y="7.4" width="2.6" height="25.2" rx="1.3" fill="#ffffff" opacity="0.5" />

    {{-- El coche, encogido desde el centro: el hueco entre el coche y las rayas es
         lo que dice que está aparcado entre ellas. Pegado a las rayas sólo se ve
         una mancha blanca. --}}
    <g transform="translate(20 20) scale(0.82) translate(-20 -20)">
        <path fill="#ffffff"
              d="M20 10.2c3.1 0 5.1 1.1 5.6 3l.5 3.9c.3 2 .3 5.8 0 7.8l-.5 3.2c-.4 1.8-2.4 2.7-5.6 2.7s-5.2-.9-5.6-2.7l-.5-3.2c-.3-2-.3-5.8 0-7.8l.5-3.9c.5-1.9 2.5-3 5.6-3Z" />
        <rect x="11.6" y="17.1" width="2.3" height="2.9" rx="1.15" fill="#ffffff" />
        <rect x="26.1" y="17.1" width="2.3" height="2.9" rx="1.15" fill="#ffffff" />

        {{-- Parabrisas y luneta. --}}
        <path fill="#152dda" fill-opacity="0.62" d="M16.5 14.6h7l.9 3.1h-8.8z" />
        <path fill="#152dda" fill-opacity="0.62" d="M15.8 24.9h8.4l-.8 2.6h-6.8z" />
    </g>
</svg>
