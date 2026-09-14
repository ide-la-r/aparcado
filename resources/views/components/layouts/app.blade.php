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

    {{-- El pie reparte los enlaces en tres columnas en lugar de dos: con dos, la
         mitad derecha se quedaba vacía y la lista legal salía una tira de seis. Y
         el texto va un punto más claro y un punto más grande: a blanco al 40 % y
         doce píxeles no se leía, se adivinaba. --}}
    <footer class="mt-20">
        <x-dark-section tone="foot">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-20">
            <div class="grid gap-10 lg:grid-cols-[1.15fr_1.85fr] lg:gap-16">
                <div class="max-w-sm">
                    <div class="flex items-center gap-3">
                        <x-logo class="h-10 w-10" />
                        <span class="font-display text-xl font-bold tracking-tight">Aparcado</span>
                    </div>

                    <p class="mt-4 text-white/70">
                        Alquiler de coches entre particulares. El que está parado en la puerta
                        de tu vecino puede ser el tuyo este fin de semana.
                    </p>

                    <a href="{{ route('cars.index') }}" class="link-underline mt-5 inline-flex items-center gap-1.5 text-white">
                        Ver los coches
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13.2 5.4 11.8 6.8 16 11H4v2h12l-4.2 4.2 1.4 1.4L20 12l-6.8-6.6Z" />
                        </svg>
                    </a>
                </div>

                {{-- En el móvil las tres listas van en rejilla, no una debajo de
                     otra: apiladas dejaban un pie de mil cien píxeles que hay que
                     recorrer entero con el dedo. --}}
                <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 sm:gap-10">
                <nav>
                    <p class="text-sm font-semibold text-white">La web</p>
                    <ul class="mt-4 space-y-2.5 text-white/65">
                        <li><a class="transition hover:text-white" href="{{ route('cars.index') }}">Coches</a></li>
                        <li><a class="transition hover:text-white" href="{{ route('home') }}#como-funciona">Cómo funciona</a></li>
                        <li><a class="transition hover:text-white" href="{{ route('pages.help') }}">Ayuda</a></li>
                        @guest
                            <li><a class="transition hover:text-white" href="{{ route('register') }}">Crear cuenta</a></li>
                            <li><a class="transition hover:text-white" href="{{ route('login') }}">Entrar</a></li>
                        @else
                            <li><a class="transition hover:text-white" href="{{ route('my-cars.index') }}">Mis coches</a></li>
                            <li><a class="transition hover:text-white" href="{{ route('bookings.index') }}">Mis reservas</a></li>
                        @endguest
                    </ul>
                </nav>

                <nav>
                    <p class="text-sm font-semibold text-white">El proyecto</p>
                    <ul class="mt-4 space-y-2.5 text-white/65">
                        <li><a class="transition hover:text-white" href="{{ route('pages.about') }}">Sobre Aparcado</a></li>
                        <li><a class="transition hover:text-white" href="{{ route('pages.credits') }}">Créditos de las fotos</a></li>
                        <li><a class="transition hover:text-white" href="{{ route('pages.contact') }}">Contacto</a></li>
                        <li>
                            <a class="transition hover:text-white" href="https://github.com/ide-la-r/aparcado"
                               rel="noopener" target="_blank">El código, en GitHub</a>
                        </li>
                    </ul>
                </nav>

                <nav>
                    <p class="text-sm font-semibold text-white">Lo legal</p>
                    <ul class="mt-4 space-y-2.5 text-white/65">
                        <li><a class="transition hover:text-white" href="{{ route('pages.legal') }}">Aviso legal</a></li>
                        <li><a class="transition hover:text-white" href="{{ route('pages.privacy') }}">Privacidad</a></li>
                        <li><a class="transition hover:text-white" href="{{ route('pages.cookies') }}">Cookies</a></li>
                    </ul>
                </nav>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-3 border-t border-white/10 pt-7 text-sm text-white/50 sm:mt-14 sm:flex-row sm:items-center sm:justify-between">
                <p class="max-w-xl">
                    Proyecto personal, sin ánimo de lucro y sin alquileres reales. La idea viene
                    de SocialiCar, un trabajo de fin de grado hecho en equipo.
                </p>

                <p class="shrink-0">© {{ now()->year }} Aparcado</p>
            </div>
        </div>
        </x-dark-section>
    </footer>
</body>
</html>
