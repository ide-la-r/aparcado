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
    {{-- La cabecera nace sin borde y lo gana al bajar: encima de la portada oscura
         una raya gris se ve como un recorte, y sobre el contenido blanco hace falta
         para separar. --}}
    <header x-data="{ bajado: false }"
            @scroll.window="bajado = window.scrollY > 8"
            class="sticky top-0 z-30 bg-white/80 backdrop-blur-md transition-shadow duration-300"
            :class="bajado ? 'shadow-sm ring-1 ring-neutral-900/5' : ''">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                <x-logo class="h-9 w-9 transition duration-300 group-hover:-rotate-6 group-hover:scale-105" />
                <span class="font-display text-lg font-bold tracking-tight">Aparcado</span>
            </a>

            <nav class="ml-auto hidden items-center gap-1 sm:flex">
                <a href="{{ route('cars.index') }}" class="btn btn-ghost">Coches</a>

                @auth
                    @php $unread = auth()->user()->unreadMessages(); @endphp

                    <a href="{{ route('messages.index') }}" class="btn btn-ghost">
                        Mensajes

                        @if ($unread > 0)
                            <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-600 px-1.5 text-xs font-semibold text-white">
                                {{ $unread }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('bookings.index') }}" class="btn btn-ghost">Mis reservas</a>
                    <a href="{{ route('my-cars.index') }}" class="btn btn-ghost">Mis coches</a>
                @else
                    <a href="{{ route('home') }}#como-funciona" class="btn btn-ghost">Cómo funciona</a>
                @endauth
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

        @if (session('warning'))
            <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6">
                <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900 ring-1 ring-amber-200 ring-inset">
                    {{ session('warning') }}
                </p>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="mt-20">
        <x-dark-section tone="soft">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="flex flex-col gap-10 sm:flex-row sm:justify-between">
                <div class="max-w-xs">
                    <div class="flex items-center gap-2.5">
                        <x-logo class="h-9 w-9" />
                        <span class="text-lg font-bold">Aparcado</span>
                    </div>
                    <p class="mt-4 text-sm text-white/60">
                        Alquiler de coches entre particulares. El coche que está parado en la
                        puerta de tu vecino.
                    </p>
                </div>

                <div class="grid gap-8 text-sm sm:grid-cols-2 sm:gap-14">
                    <nav>
                        <p class="text-xs font-semibold tracking-wide text-white/40 uppercase">La web</p>
                        <ul class="mt-3 space-y-2 text-white/70">
                            <li><a class="hover:text-white" href="{{ route('cars.index') }}">Coches</a></li>
                            <li><a class="hover:text-white" href="{{ route('home') }}#como-funciona">Cómo funciona</a></li>
                            <li><a class="hover:text-white" href="{{ route('pages.help') }}">Ayuda</a></li>
                            @guest
                                <li><a class="hover:text-white" href="{{ route('register') }}">Crear cuenta</a></li>
                            @else
                                <li><a class="hover:text-white" href="{{ route('my-cars.index') }}">Mis coches</a></li>
                            @endguest
                        </ul>
                    </nav>

                    <nav>
                        <p class="text-xs font-semibold tracking-wide text-white/40 uppercase">Lo legal</p>
                        <ul class="mt-3 space-y-2 text-white/70">
                            <li><a class="hover:text-white" href="{{ route('pages.about') }}">Sobre Aparcado</a></li>
                            <li><a class="hover:text-white" href="{{ route('pages.privacy') }}">Privacidad</a></li>
                            <li><a class="hover:text-white" href="{{ route('pages.cookies') }}">Cookies</a></li>
                            <li><a class="hover:text-white" href="{{ route('pages.legal') }}">Aviso legal</a></li>
                            <li><a class="hover:text-white" href="{{ route('pages.credits') }}">Créditos de las fotos</a></li>
                            <li><a class="hover:text-white" href="{{ route('pages.contact') }}">Contacto</a></li>
                        </ul>
                    </nav>
                </div>
            </div>

            <p class="mt-12 border-t border-white/10 pt-6 text-xs text-white/40">
                Proyecto personal, sin ánimo de lucro y sin alquileres reales. La idea viene
                de SocialiCar, un trabajo de fin de grado hecho en equipo.
            </p>
        </div>
        </x-dark-section>
    </footer>
</body>
</html>
