<?php

return [

    /*
     * Los planes del dueño. Lo que se compra es el puesto en el catálogo: los coches
     * de un Premium salen primero, después los de un Plus y al final los de quien no
     * paga nada. Los precios son los del TFG (9,99 y 19,99 al mes).
     *
     * El importe va en céntimos y sale de aquí, nunca del navegador: es el que se
     * compara con lo que devuelve la pasarela de pago.
     */
    'plans' => [
        'premium' => [
            'name' => 'Premium',
            'price_cents' => 1999,
            'priority' => 2,
            'blurb' => 'Tu coche, el primero de su provincia.',
        ],
        'plus' => [
            'name' => 'Plus',
            'price_cents' => 999,
            'priority' => 1,
            'blurb' => 'Tu coche, por delante de los que no llevan plan.',
        ],
    ],

    /*
     * Listas cerradas de la ficha del coche. Están aquí y no en un enum porque sólo
     * se usan para pintar los desplegables y para validar que lo que llega es una
     * de ellas.
     */
    /*
     * Los grupos en los que se enseñan los extras en la ficha del coche.
     */
    'feature_groups' => [
        'comfort' => 'Comodidad',
        'safety' => 'Seguridad',
        'connectivity' => 'Conectividad',
        'transport' => 'Carga y transporte',
        'mechanics' => 'Mecánica',
        'access' => 'Accesibilidad',
        'rules' => 'Normas del dueño',
    ],

    'fuels' => ['Gasolina', 'Diésel', 'Híbrido', 'Híbrido enchufable', 'Eléctrico', 'GLP', 'GNC'],

    'transmissions' => ['Manual', 'Automático'],

    'body_types' => ['Urbano', 'Compacto', 'Berlina', 'Familiar', 'SUV', 'Todoterreno', 'Monovolumen', 'Furgoneta', 'Descapotable', 'Coupé'],

    'parking_types' => ['Calle', 'Garaje privado', 'Parking público', 'Parcela'],

    'document_types' => ['DNI', 'NIE', 'Pasaporte'],

    /*
     * Los años que hay que tener para abrir una cuenta. El TFG no comprobaba la
     * edad en ningún sitio.
     */
    'min_age' => 18,

    /*
     * El token que comparten el servicio y el cron de GitHub Actions. Sin valor,
     * las rutas internas devuelven 404: una llave vacía no es una llave.
     */
    'internal_token' => env('INTERNAL_TASK_TOKEN'),

    /*
     * Los ficheros que sube la gente.
     *
     * El DNI y el carné van al disco **privado**: son datos sensibles y no los pide
     * ninguna pantalla, sólo quien verifica la cuenta. En el TFG vivían en una
     * carpeta pública del servidor, así que con acertar el nombre del fichero
     * cualquiera se descargaba el DNI de otro.
     *
     * La foto de perfil sí es para verse, así que va al disco público.
     */
    'uploads' => [
        /*
         * Salen del entorno porque en Render el disco del contenedor se borra en
         * cada despliegue: allí los dos apuntan a un bucket de S3 (R2 o B2, los
         * dos con 10 GB gratis) y en local a los discos de siempre.
         */
        'avatar_disk' => env('UPLOADS_PUBLIC_DISK', 'public'),
        'cars_disk' => env('UPLOADS_PUBLIC_DISK', 'public'),
        'documents_disk' => env('UPLOADS_PRIVATE_DISK', 'local'),
        'max_kilobytes' => 4096,
    ],

    /*
     * Cuántos días como máximo puede durar una reserva, y con cuánta antelación se
     * puede pedir. El TFG no ponía ningún tope y se podía reservar un coche cuatro
     * años.
     */
    'bookings' => [
        'max_days' => 60,
        'max_months_ahead' => 12,
    ],

];
