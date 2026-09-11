<x-layouts.app title="Sobre Aparcado">
    <x-page-header title="Sobre Aparcado"
                   lead="Qué es esto, de dónde viene y qué no es." />

    <x-prose>
        <h2>Qué es</h2>
        <p>
            Aparcado junta a quien tiene un coche parado con quien lo necesita un par de
            días. El dueño lo publica cuando no lo usa, otra persona lo reserva por
            fechas, y los dos hablan por el chat de la propia web antes y durante el
            alquiler.
        </p>

        <h2>De dónde viene</h2>
        <p>
            De <strong>SocialiCar</strong>, un Trabajo de Fin de Grado que hice en equipo
            con <a href="https://github.com/pma152402">@pma152402</a>. La idea, el alcance
            y las decisiones de producto son de aquel trabajo conjunto.
        </p>
        <p>
            Aquello era PHP a mano: consultas sueltas dentro de las vistas, ficheros de
            79 KB, diecisiete hojas de estilo y un pago que se resolvía entero en el
            navegador sin dejar rastro en la base de datos. Funcionaba, y aprendí
            montándolo. Esto es lo mismo hecho como toca.
        </p>

        <h2>Qué no es</h2>
        <p>
            <strong>No es un servicio real de alquiler.</strong> Es un proyecto personal,
            sin ánimo de lucro, hecho para aprender y para tenerlo enseñable. Los pagos
            apuntan al entorno de pruebas de PayPal, así que no se mueve dinero de verdad,
            y no hay ninguna empresa detrás que responda por un alquiler.
        </p>

        <h2>Cómo está hecho</h2>
        <p>
            Laravel sobre PHP, Blade con Alpine y Tailwind, SQLite en local y Postgres en
            producción. El código está a la vista en
            <a href="https://github.com/ide-la-r/aparcado">GitHub</a>, con sus tests y su
            integración continua.
        </p>
    </x-prose>
</x-layouts.app>
