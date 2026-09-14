@props(['tone' => 'full'])

{{-- El fondo de las secciones oscuras: manchas de color que flotan muy despacio y
     una capa de grano encima. El grano es lo que evita que un degradado grande se
     vea a bandas; las manchas son lo que evita que parezca un rectángulo negro.

     Tres versiones:
       · `full` — la portada: las manchas arriba y una tercera abajo.
       · `soft` — las franjas de cabecera: las mismas de arriba y nada más.
       · `foot` — el pie: las manchas **abajo**. Cuando la página ya termina en
         oscuro, el pie va pegado a la sección anterior (ver la regla de `app.css`),
         y si los dos bloques empezaran con el resplandor arriba se vería un escalón
         justo en la unión: oscuro abajo del primero, azul brillante arriba del
         segundo. Con las manchas abajo, lo que se tocan son dos zonas oscuras y la
         unión no existe.

     El distintivo `data-dark-section` no es decorativo: es lo que deja al pie
     pegarse. --}}
<section data-dark-section {{ $attributes->merge(['class' => 'relative isolate overflow-hidden bg-ink text-white grain']) }}>
    @if ($tone === 'foot')
        <div class="aurora animate-float -bottom-40 -left-32 h-[34rem] w-[34rem] bg-brand-600/40"></div>
        <div class="aurora animate-float-slow -right-16 -bottom-52 h-[26rem] w-[26rem] bg-accent-500/18"></div>
    @else
        <div class="aurora animate-float -top-40 -left-32 h-[34rem] w-[34rem] bg-brand-600/45"></div>

        {{-- El ámbar va flojo y muy arriba a propósito: en una franja alta se lee
             como un amanecer en una esquina, pero en una de doscientos píxeles lo
             que se ve es justo su centro, y con más peso deja la franja entera
             marrón. --}}
        <div class="aurora animate-float-slow -top-52 -right-16 h-[26rem] w-[26rem] bg-accent-500/20"></div>

        @if ($tone === 'full')
            <div class="aurora animate-float-slow bottom-[-14rem] left-1/3 h-[24rem] w-[24rem] bg-brand-400/20"></div>
        @endif
    @endif

    {{-- La rejilla, muy tenue, para que el fondo tenga textura. --}}
    <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
         style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 56px 56px;"
         aria-hidden="true"></div>

    <div class="relative">
        {{ $slot }}
    </div>
</section>
