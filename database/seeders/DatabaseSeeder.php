<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Datos de referencia: hacen falta siempre, también en producción.
        $this->call(ReferenceDataSeeder::class);

        // Los de ejemplo, sólo fuera de producción.
        if (! app()->isProduction()) {
            $this->call(DemoSeeder::class);
        }
    }
}
