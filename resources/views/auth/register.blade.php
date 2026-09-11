<x-layouts.app title="Crear cuenta">
    <div class="mx-auto max-w-lg px-4 py-12 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight">Crear cuenta</h1>
        <p class="mt-1.5 text-neutral-600">
            Con esto ya puedes mirar y escribir a los dueños. Para alquilar o publicar un
            coche hace falta además que comprobemos tus papeles.
        </p>

        <form method="POST" action="{{ route('register') }}" class="card mt-6 space-y-4 p-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <x-text-field name="name" label="Nombre" autocomplete="given-name" required autofocus />
                <x-text-field name="surname" label="Apellidos" autocomplete="family-name" required />
            </div>

            <x-text-field name="email" label="Correo" type="email" autocomplete="email" required />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-text-field name="phone" label="Teléfono" type="tel" autocomplete="tel" required />
                <x-text-field name="birthdate" label="Fecha de nacimiento" type="date" required
                              :hint="'Hay que tener '.config('aparcado.min_age').' años o más.'" />
            </div>

            <div class="grid gap-4 sm:grid-cols-[9rem_1fr]">
                <x-text-field name="document_type" label="Documento" :options="config('aparcado.document_types')" />
                <x-text-field name="document_number" label="Número" required />
            </div>

            <x-password-field autocomplete="new-password" hint="Ocho caracteres o más." />

            <x-password-field name="password_confirmation" label="Repite la contraseña" autocomplete="new-password" />

            <button type="submit" class="btn btn-primary w-full">Crear la cuenta</button>
        </form>

        <p class="mt-5 text-sm text-neutral-600">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="font-medium text-brand-700 hover:underline">Entra</a>.
        </p>
    </div>
</x-layouts.app>
