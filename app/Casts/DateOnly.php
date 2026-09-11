<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Un día, guardado como un día.
 *
 * El cast `date` de Laravel devuelve un Carbon al leer, pero al escribir formatea
 * con el formato de fecha y hora de la conexión, así que en SQLite una columna
 * `date` acaba guardando «2026-09-11 00:00:00». Y como SQLite no tiene tipo fecha,
 * comparar eso con una cadena es comparar texto: «2026-09-11 00:00:00» <=
 * «2026-09-11» es **falso**, porque el primero es más largo.
 *
 * El efecto es de los que no se ven: una suscripción no contaba el día que
 * empezaba, y un coche parecía libre el día en que empezaba una reserva. En
 * Postgres no pasa —ahí la columna es una fecha de verdad—, así que sólo aparecía
 * en local y en los tests.
 *
 * Guardando la fecha en seco, las comparaciones normales valen en las dos bases de
 * datos y siguen usando el índice, que es lo que `whereDate()` habría roto.
 *
 * Una arista de Eloquent que conviene saber: cuando a un atributo con cast propio
 * se le asigna un **objeto**, Eloquent guarda ese objeto tal cual en su caché de
 * casts (`setClassCastableAttribute`), así que leerlo en la misma petición devuelve
 * lo que se le pasó sin pasar por `get()`. O sea: asignar un `DateTime` devuelve un
 * `DateTime`, no un Carbon, y una llamada a `toDateString()` revienta. Por eso las
 * fechas se asignan **como cadena** («2026-09-11»), que es además la forma en que
 * llegan de un formulario.
 */
class DateOnly implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        return $value === null ? null : Carbon::parse($value)->startOfDay();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : Carbon::parse($value)->toDateString();
    }
}
