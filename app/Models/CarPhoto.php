<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['path', 'position'])]
class CarPhoto extends Model
{
    /** @return BelongsTo<Car, $this> */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * La dirección pública de la foto.
     *
     * Hay dos orígenes y no se resuelven igual. Las de los coches de ejemplo van
     * en el repositorio, dentro de `public/demo`, porque el disco del contenedor
     * de Render se borra en cada despliegue y el catálogo que se enseña no puede
     * quedarse sin fotos. Las que sube la gente van al disco de subidas, que en
     * producción es un bucket.
     *
     * Esto vive aquí, y no en las vistas, porque las vistas llamaban a
     * `Storage::url()` a secas: ese atajo usa el disco **por defecto**, no el de
     * las subidas. Mientras los dos son locales da igual, pero el día que las
     * fotos se guarden en un bucket todas las direcciones saldrían mal a la vez.
     */
    public function url(): string
    {
        if (str_starts_with($this->path, 'demo/')) {
            return asset($this->path);
        }

        return Storage::disk(config('aparcado.uploads.cars_disk'))->url($this->path);
    }
}
