{{-- Los estilos del texto largo en un sitio, para no repetir la misma lista de
     clases en seis páginas legales. --}}
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
    <div {{ $attributes->merge(['class' => '
        space-y-4 text-neutral-700
        [&_h2]:mt-10 [&_h2]:text-lg [&_h2]:font-bold [&_h2]:tracking-tight [&_h2]:text-neutral-900
        [&_h2:first-child]:mt-0
        [&_h3]:mt-6 [&_h3]:font-semibold [&_h3]:text-neutral-900
        [&_ul]:list-disc [&_ul]:space-y-1.5 [&_ul]:pl-5
        [&_a]:font-medium [&_a]:text-brand-700 [&_a]:underline [&_a]:decoration-brand-300 [&_a]:underline-offset-2
        [&_a:hover]:decoration-brand-600
        [&_strong]:font-semibold [&_strong]:text-neutral-900
        [&_code]:rounded [&_code]:bg-neutral-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-sm
    ']) }}>
        {{ $slot }}
    </div>
</div>
