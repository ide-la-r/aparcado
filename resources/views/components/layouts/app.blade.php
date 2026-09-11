@props(['title' => null])

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1c3fed">

    <title>{{ $title ? $title.' · Aparcado' : 'Aparcado · Alquila el coche de tu vecino' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col">
    <header class="sticky top-0 z-30 border-b border-neutral-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <x-logo />
                <span class="text-lg font-bold tracking-tight">Aparcado</span>
            </a>

            <nav class="ml-auto hidden items-center gap-1 sm:flex">
                <a href="{{ route('cars.index') }}" class="btn btn-ghost">Coches</a>
                <a href="{{ route('home') }}#como-funciona" class="btn btn-ghost">Cómo funciona</a>
            </nav>

            <div class="ml-auto flex items-center gap-2 sm:ml-0">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-ghost">Entrar</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Crear cuenta</a>
                @else
                    <a href="{{ route('profile.show') }}" class="btn btn-ghost">
                        <span class="hidden sm:inline">Hola,&nbsp;</span>{{ auth()->user()->name }}

                        {{-- Un punto para que no se le olvide: sin papeles no puede
                             alquilar ni publicar, y es fácil dejarlo a medias. --}}
                        @unless (auth()->user()->isVerified())
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500" title="Te faltan los papeles"></span>
                        @endunless
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Salir</button>
                    </form>
                @endguest
            </div>
        </div>
    </header>

    <main class="flex-1">
        @if (session('status'))
            <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6">
                <p class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200 ring-inset">
                    {{ session('status') }}
                </p>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-neutral-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <div class="flex flex-col gap-8 sm:flex-row sm:justify-between">
                <div class="max-w-xs">
                    <div class="flex items-center gap-2.5">
                        <x-logo class="h-8 w-8 text-sm" />
                        <span class="font-bold">Aparcado</span>
                    </div>
                    <p class="mt-3 text-sm text-neutral-600">
                        Alquiler de coches entre particulares. El coche que está parado en la
                        puerta de tu vecino.
                    </p>
                </div>

                <nav class="text-sm">
                    <p class="font-semibold text-neutral-900">Aparcado</p>
                    <ul class="mt-3 space-y-2 text-neutral-600">
                        <li><a class="hover:text-neutral-900" href="{{ route('cars.index') }}">Coches</a></li>
                        <li><a class="hover:text-neutral-900" href="{{ route('home') }}#como-funciona">Cómo funciona</a></li>
                    </ul>
                </nav>
            </div>

            <p class="mt-10 text-xs text-neutral-500">
                Proyecto personal, sin ánimo de lucro. La idea viene de SocialiCar, un trabajo
                de fin de grado hecho en equipo.
            </p>
        </div>
    </footer>
</body>
</html>
