<x-layouts.app title="Entrar">
    <div class="mx-auto max-w-md px-4 py-12 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight">Entrar</h1>
        <p class="mt-1.5 text-neutral-600">Con la cuenta que ya tienes.</p>

        <form method="POST" action="{{ route('login') }}" class="card mt-6 space-y-4 p-5">
            @csrf

            <x-text-field name="email" label="Correo" type="email" autocomplete="username" required autofocus />

            <x-password-field autocomplete="current-password" />

            <label class="flex items-center gap-2 text-sm text-neutral-700">
                <input type="checkbox" name="remember" value="1"
                       class="h-4 w-4 rounded border-neutral-300 text-brand-600 focus:ring-brand-600">
                No cerrar la sesión
            </label>

            <button type="submit" class="btn btn-primary w-full">Entrar</button>
        </form>

        <p class="mt-5 text-sm text-neutral-600">
            ¿Todavía no tienes cuenta?
            <a href="{{ route('register') }}" class="font-medium text-brand-700 hover:underline">Créate una</a>.
        </p>
    </div>
</x-layouts.app>
