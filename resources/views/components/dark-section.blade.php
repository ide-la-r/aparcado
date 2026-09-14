@props(['tone' => 'full'])

{{-- El fondo de las secciones oscuras: dos manchas de color que flotan muy
     despacio y una capa de grano encima. El grano es lo que evita que un degradado
     grande se vea a bandas; las manchas son lo que evita que parezca un rectángulo
     negro. --}}
<section {{ $attributes->merge(['class' => 'relative isolate overflow-hidden bg-ink text-white grain']) }}>
    <div class="aurora animate-float -top-40 -left-32 h-[34rem] w-[34rem] bg-brand-600/45"></div>
    <div class="aurora animate-float-slow -top-24 right-0 h-[26rem] w-[26rem] bg-accent-500/25"></div>

    @if ($tone === 'full')
        <div class="aurora animate-float-slow bottom-[-14rem] left-1/3 h-[24rem] w-[24rem] bg-brand-400/20"></div>
    @endif

    {{-- La rejilla, muy tenue, para que el fondo tenga textura. --}}
    <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
         style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 56px 56px;"
         aria-hidden="true"></div>

    <div class="relative">
        {{ $slot }}
    </div>
</section>
