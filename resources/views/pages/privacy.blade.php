<x-layouts.app title="Privacidad">
    <x-page-header title="Privacidad"
                   lead="Qué se guarda, para qué, y dónde." />

    <x-prose>
        <h2>Qué se guarda</h2>
        <ul>
            <li><strong>De tu cuenta:</strong> nombre, apellidos, correo, teléfono, fecha de
                nacimiento y el tipo y número de tu documento de identidad.</li>
            <li><strong>La contraseña, cifrada.</strong> Se guarda un resumen del que no se
                puede volver atrás: ni nosotros podemos leerla.</li>
            <li><strong>Tus papeles:</strong> la foto del documento de identidad y la del
                carné de conducir, si los subes.</li>
            <li><strong>De tus coches:</strong> lo que publicas del anuncio, incluidas la
                dirección y sus coordenadas.</li>
            <li><strong>De tus alquileres:</strong> fechas, importes y el estado de cada
                reserva y de cada cobro.</li>
            <li><strong>Tus conversaciones</strong> con las otras personas.</li>
        </ul>

        <h2>Los papeles no se publican</h2>
        <p>
            La foto de tu documento y la de tu carné se guardan en un <strong>disco
            privado</strong>, fuera de lo que sirve el servidor web. No hay ninguna
            dirección pública que lleve a ellas y no las pide ninguna pantalla: sólo las
            ve quien comprueba la cuenta. Cuando vuelves a subir una, la anterior se
            borra.
        </p>
        <p>
            La foto de perfil sí es pública, porque su razón de ser es que la vea la otra
            persona.
        </p>

        <h2>Tu dirección tampoco</h2>
        <p>
            La calle y el número de donde guardas el coche <strong>no salen en la
            ficha</strong>. Se enseña la ciudad, la provincia y una zona aproximada en el
            mapa; el punto de recogida lo dices tú por el chat cuando hay una reserva.
        </p>
        <p>
            Para dibujar esa zona hacen falta unas coordenadas, y para eso se le pregunta
            la dirección a <a href="https://photon.komoot.io">Photon</a>, el buscador de
            OpenStreetMap. Al mapa que se dibuja en la ficha sólo le llegan las
            coordenadas <strong>redondeadas a dos decimales</strong>, poco más de un
            kilómetro: ni el mapa sabe dónde aparcas.
        </p>

        <h2>Quién más ve algo</h2>
        <ul>
            <li><strong>PayPal</strong>, cuando pagas: el importe y el concepto. Los datos
                de tu tarjeta los pones en PayPal y no pasan por aquí en ningún momento.</li>
            <li><strong>Photon</strong> (OpenStreetMap), la dirección que escribes al
                publicar un coche, para convertirla en coordenadas.</li>
            <li><strong>OpenStreetMap</strong>, la zona aproximada, al cargar el mapa.</li>
        </ul>
        <p>No hay analítica, ni publicidad, ni nada que te siga por otras webs.</p>

        <h2>Tus derechos</h2>
        <p>
            Puedes ver y cambiar tus datos desde tu perfil, y pedir que se borre la cuenta
            escribiendo a la dirección de <a href="{{ route('pages.contact') }}">contacto</a>.
            Lo que se queda al borrar una cuenta es el registro de los cobros, porque de
            eso hay obligación de llevar cuentas.
        </p>

        <h2>Y lo importante</h2>
        <p>
            Esto es un <a href="{{ route('pages.about') }}">proyecto personal</a>, no un
            servicio real. No subas aquí nada que no querrías perder, y ten en cuenta que
            no hay una empresa detrás con un departamento que responda.
        </p>
    </x-prose>
</x-layouts.app>
