<?php

/*
 * Las fotos de los coches de ejemplo salen de Wikimedia Commons y son de los
 * modelos de verdad: coches fotografiados en la calle, que es como se ven los
 * anuncios de verdad. Casi todas llevan licencia CC BY-SA, que **obliga a citar a
 * quien las hizo**: eso es lo que hay aquí y lo que pinta /creditos.
 *
 * Viven en `public/demo` y no en el disco de subidas a propósito: ese disco se
 * borra en cada despliegue de Render, y el catálogo se quedaría sin fotos al
 * segundo despliegue.
 *
 * **La matrícula es la que manda.** Es lo único que identifica a un coche de
 * ejemplo en cualquier base de datos: los ids dependen del orden en que se sembró,
 * y una corrección de datos atada al id acaba tocando al que no es. `DemoSeeder`
 * mira aquí para saber qué fotos le tocan a cada coche, y la tarea que las repara
 * en producción, también.
 */
return [

    1 => [
        'plate' => '1834 KLM',
        'model' => 'Seat Ibiza',
        'photos' => [
            ['file' => 'demo/coche-1.jpg', 'author' => 'Johannes Maximilian', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:Seat_Ibiza_FR_IAA_2019_JM_1120.jpg'],
        ],
    ],

    2 => [
        'plate' => '4471 BCD',
        'model' => 'Volkswagen Golf',
        'photos' => [
            ['file' => 'demo/coche-2.jpg', 'author' => 'Vauxford', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:2020_Volkswagen_Golf_Style_1.5_Front.jpg'],
        ],
    ],

    3 => [
        'plate' => '9126 FGH',
        'model' => 'Dacia Duster',
        'photos' => [
            ['file' => 'demo/coche-3.jpg', 'author' => 'Vauxford', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:2018_Dacia_Duster_Comfort_1.6.jpg'],
        ],
    ],

    4 => [
        'plate' => '2287 JKP',
        'model' => 'Toyota Corolla',
        'photos' => [
            ['file' => 'demo/coche-4.jpg', 'author' => 'Alexander-93', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:2023_Toyota_Corolla_Hybrid_(E210)_hatchback_IMG_9877.jpg'],
        ],
    ],

    5 => [
        'plate' => '6693 LMN',
        'model' => 'Renault Clio',
        'photos' => [
            ['file' => 'demo/coche-5.jpg', 'author' => 'Alexander Migl', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:Renault_Clio_V_Sindelfingen_2020_IMG_2304.jpg'],
        ],
    ],

    6 => [
        'plate' => '5518 PQR',
        'model' => 'Volkswagen Tiguan',
        'photos' => [
            ['file' => 'demo/coche-6.jpg', 'author' => 'SSJF01', 'licence' => 'CC BY 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:Volkswagen_Tiguan_(2016%E2%80%932021).png'],
        ],
    ],

    7 => [
        'plate' => '3342 RST',
        'model' => 'Peugeot 208',
        'photos' => [
            ['file' => 'demo/coche-7.jpg', 'author' => 'Alexander-93', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:Peugeot_e-208_facelift_Auto_Zuerich_2023_1X7A1209.jpg'],
        ],
    ],

    8 => [
        'plate' => '7705 TUV',
        'model' => 'Citroën Berlingo',
        'photos' => [
            ['file' => 'demo/coche-8.jpg', 'author' => 'Spielvogel', 'licence' => 'CC0', 'page' => 'https://commons.wikimedia.org/wiki/File:Citroen_Berlingo_2018_panel_van_left.jpg'],
        ],
    ],

    9 => [
        'plate' => '1159 VWX',
        'model' => 'Seat León',
        'photos' => [
            ['file' => 'demo/coche-9.jpg', 'author' => 'Alexander Migl', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:SEAT_Leon_Mk4_1X7A5845.jpg'],
        ],
    ],

    10 => [
        'plate' => '8820 XYZ',
        'model' => 'Fiat 500',
        'photos' => [
            ['file' => 'demo/coche-10.jpg', 'author' => 'Vauxford', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:2016_Fiat_500_Lounge_1.2_Rear.jpg'],
        ],
    ],

    11 => [
        'plate' => '4408 ZAB',
        'model' => 'Toyota RAV4',
        'photos' => [
            ['file' => 'demo/coche-11.jpg', 'author' => 'Dinkun Chen', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:TOYOTA_RAV4_HYBRID_(XA50)_China.jpg'],
        ],
    ],

    12 => [
        'plate' => '6674 BCE',
        'model' => 'Renault Mégane',
        'photos' => [
            ['file' => 'demo/coche-12.jpg', 'author' => 'LuvsMG481', 'licence' => 'CC BY-SA 4.0', 'page' => 'https://commons.wikimedia.org/wiki/File:2019_Renault_Megane_Zen_rear.jpg'],
        ],
    ],

];
