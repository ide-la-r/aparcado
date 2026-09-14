<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarPhoto;
use Tests\TestCase;

/**
 * Las fotos vienen de dos sitios que no se resuelven igual, y cuál es cuál lo
 * decide `CarPhoto::url()`.
 */
class CarPhotoUrlTest extends TestCase
{
    public function test_a_demo_photo_is_served_from_the_repository_even_when_uploads_go_elsewhere(): void
    {
        // Con las subidas apuntando a un bucket, que es como va a estar en Render.
        $this->useACdnForUploads();

        $photo = new CarPhoto(['path' => 'demo/coche-3.jpg']);

        /*
         * Las de ejemplo van dentro de `public/` y tienen que seguir saliendo de
         * ahí: el disco del contenedor de Render se borra en cada despliegue, así
         * que si estas colgaran del disco de subidas el catálogo que se enseña se
         * quedaría sin fotos al segundo despliegue.
         */
        $this->assertSame(asset('demo/coche-3.jpg'), $photo->url());
        $this->assertStringNotContainsString('cdn.aparcado.test', $photo->url());
    }

    public function test_an_uploaded_photo_comes_from_the_uploads_disk_and_not_the_default_one(): void
    {
        /*
         * Esto es el fallo que arregla el método. Las vistas llamaban a
         * `Storage::url()` a secas, que usa el disco **por defecto**; las fotos, en
         * cambio, se guardan en el de subidas. Mientras los dos son locales
         * coincide y no se nota, pero en cuanto las subidas se van a un bucket
         * —que es el plan en Render— todas las direcciones apuntarían al sitio
         * equivocado a la vez.
         */
        $this->useACdnForUploads();

        $photo = new CarPhoto(['path' => 'cars/9/frente.jpg']);

        $this->assertSame('https://cdn.aparcado.test/fotos/cars/9/frente.jpg', $photo->url());
    }

    public function test_the_catalogue_prints_the_photo_of_the_car(): void
    {
        $car = Car::factory()->create();
        $car->photos()->create(['path' => 'demo/coche-7.jpg', 'position' => 0]);

        $this->get(route('cars.index'))
            ->assertOk()
            ->assertSee(asset('demo/coche-7.jpg'), escape: false);
    }

    /** Las subidas en un disco aparte del de por defecto, como en producción. */
    private function useACdnForUploads(): void
    {
        config([
            'filesystems.default' => 'local',
            'filesystems.disks.fotos' => [
                'driver' => 'local',
                'root' => storage_path('app/fotos'),
                'url' => 'https://cdn.aparcado.test/fotos',
            ],
            'aparcado.uploads.cars_disk' => 'fotos',
        ]);
    }
}
