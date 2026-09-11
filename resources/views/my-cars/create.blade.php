<x-layouts.app title="Publicar un coche">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Publicar un coche</h1>
        <p class="mt-1.5 text-neutral-600">
            Cuanto más cuentes, menos preguntas te van a hacer por el chat.
        </p>

        <x-car-form :action="route('my-cars.store')" :provinces="$provinces" :features="$features" :groups="$groups">
            Publicar
        </x-car-form>
    </div>
</x-layouts.app>
