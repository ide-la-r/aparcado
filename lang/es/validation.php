<?php

/*
 * Sólo las reglas que la aplicación usa de verdad. Las que falten las coge del
 * inglés, que es el idioma de respaldo: un fichero a medias funciona y crece con
 * los formularios, en lugar de traducir de golpe cien mensajes que nadie ve.
 */
return [

    'after_or_equal' => 'La fecha de :attribute no puede ser anterior a :date.',
    'before_or_equal' => 'La fecha de :attribute no puede ser posterior a :date.',
    'confirmed' => 'Las dos veces que has escrito la :attribute no coinciden.',
    'date' => 'La fecha de :attribute no es una fecha.',
    'date_format' => 'La fecha de :attribute no tiene el formato :format.',
    'email' => 'Eso no parece un correo electrónico.',
    'exists' => 'No encontramos :attribute.',
    'in' => 'Eso no es una opción de :attribute.',
    'integer' => 'El campo :attribute tiene que ser un número entero.',
    'max' => [
        'array' => 'No puedes elegir más de :max.',
        'file' => 'El archivo no puede pasar de :max kilobytes.',
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'string' => 'El campo :attribute no puede pasar de :max caracteres.',
    ],
    'min' => [
        'array' => 'Tienes que elegir al menos :min.',
        'numeric' => 'El campo :attribute no puede ser menor que :min.',
        'string' => 'El campo :attribute tiene que tener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute tiene que ser un número.',
    'required' => 'Falta :attribute.',
    'required_with' => 'Falta :attribute.',
    'unique' => 'Ya hay una cuenta con ese :attribute.',

    'attributes' => [
        'birthdate' => 'la fecha de nacimiento',
        'brand' => 'la marca',
        'city' => 'la ciudad',
        'description' => 'la descripción',
        'email' => 'el correo',
        'from' => 'entrada',
        'name' => 'el nombre',
        'password' => 'contraseña',
        'phone' => 'el teléfono',
        'plate' => 'la matrícula',
        'price_cents' => 'el precio',
        'province' => 'esa provincia',
        'surname' => 'los apellidos',
        'to' => 'salida',
    ],

];
