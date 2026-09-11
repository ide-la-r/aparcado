<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Los datos que no son de nadie y hacen falta siempre: las provincias y el catálogo
 * de extras. Van juntos en un solo sembrador porque `migrate:fresh --seeder=` sólo
 * acepta una clase, y es el que usan los tests.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            FeatureSeeder::class,
        ]);
    }
}
