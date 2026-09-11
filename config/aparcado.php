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
     * Cuántos días como máximo puede durar una reserva, y con cuánta antelación se
     * puede pedir. El TFG no ponía ningún tope y se podía reservar un coche cuatro
     * años.
     */
    'bookings' => [
        'max_days' => 60,
        'max_months_ahead' => 12,
    ],

];
