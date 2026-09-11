@props(['title' => null])

<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0f1f">

    <title>{{ $title ? $title.' · Aparcado' : 'Aparcado' }}</title>

    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full flex-col bg-ink ink-glow text-white">
    {{--
        Un armazón aparte para las páginas de error, y no es capricho: el armazón
        normal pinta la cabecera con el contador de mensajes sin leer, y eso es una
        consulta. Una página de error 500 que consulta la base de datos revienta
        justo cuando el motivo del error es que la base de datos no contesta, y
        entonces el usuario ve la pantalla blanca de PHP en lugar del aviso.

        Aquí no hay sesión, ni relaciones, ni JavaScript: sólo HTML y CSS.
    --}}
    <main class="flex flex-1 items-center justify-center px-4 py-16">
        <div class="w-full max-w-md text-center">
            <a href="/" class="inline-flex items-center gap-2.5">
                <x-logo class="h-10 w-10" />
                <span class="text-xl font-bold">Aparcado</span>
            </a>

            {{ $slot }}
        </div>
    </main>
</body>
</html>
