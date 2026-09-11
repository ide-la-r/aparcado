<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

/**
 * Los veinte extras que tenía el TFG, que allí eran veinte columnas booleanas de
 * `extras_coche`. Uno se ha quedado por el camino con otro nombre: lo que estaba
 * guardado como «android_carplay» es Android Auto — CarPlay es sólo de Apple.
 */
class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['aire-acondicionado', 'Aire acondicionado', 'comfort'],
            ['control-crucero', 'Control de crucero', 'comfort'],
            ['asientos-calefactables', 'Asientos calefactables', 'comfort'],
            ['sensores-aparcamiento', 'Sensores de aparcamiento', 'safety'],
            ['camara-trasera', 'Cámara trasera', 'safety'],
            ['isofix', 'Fijación Isofix', 'safety'],
            ['bluetooth', 'Bluetooth', 'connectivity'],
            ['gps', 'GPS', 'connectivity'],
            ['wifi', 'Wifi', 'connectivity'],
            ['apple-carplay', 'Apple CarPlay', 'connectivity'],
            ['android-auto', 'Android Auto', 'connectivity'],
            ['baca', 'Baca', 'transport'],
            ['portaequipajes', 'Portaequipajes', 'transport'],
            ['portabicicletas', 'Portabicicletas', 'transport'],
            ['portaesquis', 'Portaesquís', 'transport'],
            ['bola-remolque', 'Bola de remolque', 'transport'],
            ['traccion-total', 'Tracción total (4x4)', 'mechanics'],
            ['movilidad-reducida', 'Adaptado a movilidad reducida', 'access'],
            ['mascotas', 'Se admiten mascotas', 'rules'],
            ['fumar', 'Se puede fumar', 'rules'],
        ];

        Feature::query()->upsert(
            array_map(fn (array $row, int $index) => [
                'slug' => $row[0],
                'name' => $row[1],
                'group' => $row[2],
                'position' => $index,
            ], $features, array_keys($features)),
            ['slug'],
            ['name', 'group', 'position'],
        );
    }
}
