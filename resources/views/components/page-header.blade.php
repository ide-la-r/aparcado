@props(['title', 'lead' => null])

<section class="relative isolate overflow-hidden bg-ink ink-glow text-white">
    <div class="relative mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-20">
        <h1 class="text-3xl font-bold tracking-tight text-balance sm:text-4xl">{{ $title }}</h1>

        @if ($lead)
            <p class="mt-4 max-w-xl text-lg text-white/60">{{ $lead }}</p>
        @endif
    </div>
</section>
