<x-layouts.app title="Aviso legal">
    <x-page-header title="Aviso legal"
                   lead="Quién está detrás y con qué condiciones se usa esto." />

    <x-prose>
        <h2>Quién</h2>
        <p>
            Aparcado es un proyecto personal de Ismael de la Rosa Guerrero, sin ánimo de
            lucro y sin actividad comercial. No hay ninguna empresa detrás. Para cualquier
            cosa, la <a href="{{ route('pages.contact') }}">página de contacto</a>.
        </p>

        <h2>Qué es y qué no</h2>
        <p>
            Esta web es una <strong>demostración</strong>. Ni intermedia alquileres reales,
            ni cobra comisiones, ni responde de lo que pase entre dos personas que se
            pongan de acuerdo por aquí. Los pagos apuntan al entorno de pruebas de PayPal,
            así que no se mueve dinero de verdad.
        </p>
        <p>
            Un alquiler de coche entre particulares de verdad necesita un seguro que lo
            cubra y un contrato entre las partes. Aquí no se facilita ninguna de las dos
            cosas.
        </p>

        <h2>Lo que publicas</h2>
        <p>
            Lo que subes sigue siendo tuyo, y eres responsable de tenerlo derecho a subir:
            las fotos de un coche que es tuyo, y datos que sean ciertos. Se pueden retirar
            los anuncios que no cumplan eso.
        </p>

        <h2>El código</h2>
        <p>
            El código de la aplicación está en
            <a href="https://github.com/ide-la-r/aparcado">GitHub</a>. La idea original
            viene de un Trabajo de Fin de Grado hecho en equipo, y eso está contado en
            <a href="{{ route('pages.about') }}">Sobre Aparcado</a>.
        </p>

        <h2>Legislación</h2>
        <p>Se aplica la legislación española.</p>
    </x-prose>
</x-layouts.app>
