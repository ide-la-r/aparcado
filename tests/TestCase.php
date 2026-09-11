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
}
