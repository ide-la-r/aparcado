@props(['title', 'lead' => null])

<x-dark-section tone="soft">
    <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-20">
        <h1 class="text-3xl font-bold text-balance sm:text-5xl">{{ $title }}</h1>

        @if ($lead)
            <p class="mt-5 max-w-xl text-lg text-white/60">{{ $lead }}</p>
        @endif
    </div>
</x-dark-section>
