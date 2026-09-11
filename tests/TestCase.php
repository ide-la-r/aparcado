<?php

namespace Tests;

use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Las provincias y los extras son datos de referencia: sin ellos no se puede ni
     * crear un coche, porque la provincia es una clave ajena. Se siembran en todos
     * los tests, igual que en producción.
     */
    protected string $seeder = ReferenceDataSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Los tests no dependen de que alguien haya compilado los assets. `@vite`
         * revienta si no encuentra `public/build/manifest.json`, y ese directorio no
         * se versiona: en el servidor de integración fallaban los seis tests de
         * vistas con un error que no hablaba de vistas. Que el CSS y el JavaScript
         * compilan lo comprueba su propio trabajo en la CI.
         */
        $this->withoutVite();
    }
}
