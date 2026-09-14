<?php

namespace App\Support;

use App\Models\Car;

/**
 * Las fotos de los coches de ejemplo, atadas a la matrícula.
 *
 * Existe para que el reparto de fotos esté en un solo sitio: lo usa `DemoSeeder`
 * al sembrar y lo usa la tarea que las repara en producción. Cuando el mismo
 * criterio se escribe dos veces, una de las dos copias se queda atrás.
 */
class DemoPhotos
{
    /**
     * Las rutas que le tocan a una matrícula, en orden. Vacío si esa matrícula no
     * es de ningún coche de ejemplo.
     *
     * @return list<string>
     */
    public static function forPlate(string $plate): array
    {
        foreach (config('demo_photos') as $entry) {
            if ($entry['plate'] === $plate) {
                return array_column($entry['photos'], 'file');
            }
        }

        return [];
    }

    /**
     * Vuelve a dejar cada coche de ejemplo con sus fotos y devuelve cuántos se han
     * tocado.
     *
     * Hace falta porque los datos de ejemplo se sembraron en producción cuando las
     * fotos aún no existían, y quedaron apuntando a ficheros que no están: el
     * catálogo entero se veía con el hueco de relleno. Sembrar otra vez no vale
     * —`seedDemo` se niega en cuanto hay coches, y con razón—, así que esto repara
     * lo que ya está sin tocar nada más.
     *
     * Se puede llamar las veces que haga falta: deja siempre el mismo resultado.
     */
    public static function refresh(): int
    {
        $cars = 0;

        foreach (config('demo_photos') as $entry) {
            $car = Car::query()->where('plate', $entry['plate'])->first();

            if ($car === null) {
                continue;
            }

            /*
             * Sólo se borran las de ejemplo. Si alguien ha subido una foto de
             * verdad a uno de estos coches, esa no es nuestra y se queda.
             */
            $car->photos()->where('path', 'like', 'demo/%')->delete();

            foreach (self::forPlate($entry['plate']) as $position => $path) {
                $car->photos()->create(['path' => $path, 'position' => $position]);
            }

            $cars++;
        }

        return $cars;
    }
}
