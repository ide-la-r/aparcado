@props(['code', 'title', 'text'])

<x-layouts.bare :title="$title">
    <p class="mt-10 text-7xl font-bold tracking-tight text-white/15">{{ $code }}</p>

    <h1 class="mt-2 text-2xl font-bold tracking-tight">{{ $title }}</h1>

    <p class="mt-3 text-white/60">{{ $text }}</p>

    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="/" class="btn btn-primary">Ir a la portada</a>
        <a href="/coches" class="btn btn-on-dark">Ver coches</a>
    </div>
</x-layouts.bare>
