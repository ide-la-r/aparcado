<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

/**
 * Las 52 provincias con su código del INE, que es el mismo que usan los ficheros
 * oficiales. Son datos de referencia: se siembran siempre, también en los tests.
 */
class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['01', 'Álava', 'País Vasco'],
            ['02', 'Albacete', 'Castilla-La Mancha'],
            ['03', 'Alicante', 'Comunidad Valenciana'],
            ['04', 'Almería', 'Andalucía'],
            ['05', 'Ávila', 'Castilla y León'],
            ['06', 'Badajoz', 'Extremadura'],
            ['07', 'Baleares', 'Islas Baleares'],
            ['08', 'Barcelona', 'Cataluña'],
            ['09', 'Burgos', 'Castilla y León'],
            ['10', 'Cáceres', 'Extremadura'],
            ['11', 'Cádiz', 'Andalucía'],
            ['12', 'Castellón', 'Comunidad Valenciana'],
            ['13', 'Ciudad Real', 'Castilla-La Mancha'],
            ['14', 'Córdoba', 'Andalucía'],
            ['15', 'A Coruña', 'Galicia'],
            ['16', 'Cuenca', 'Castilla-La Mancha'],
            ['17', 'Girona', 'Cataluña'],
            ['18', 'Granada', 'Andalucía'],
            ['19', 'Guadalajara', 'Castilla-La Mancha'],
            ['20', 'Gipuzkoa', 'País Vasco'],
            ['21', 'Huelva', 'Andalucía'],
            ['22', 'Huesca', 'Aragón'],
            ['23', 'Jaén', 'Andalucía'],
            ['24', 'León', 'Castilla y León'],
            ['25', 'Lleida', 'Cataluña'],
            ['26', 'La Rioja', 'La Rioja'],
            ['27', 'Lugo', 'Galicia'],
            ['28', 'Madrid', 'Comunidad de Madrid'],
            ['29', 'Málaga', 'Andalucía'],
            ['30', 'Murcia', 'Región de Murcia'],
            ['31', 'Navarra', 'Comunidad Foral de Navarra'],
            ['32', 'Ourense', 'Galicia'],
            ['33', 'Asturias', 'Principado de Asturias'],
            ['34', 'Palencia', 'Castilla y León'],
            ['35', 'Las Palmas', 'Canarias'],
            ['36', 'Pontevedra', 'Galicia'],
            ['37', 'Salamanca', 'Castilla y León'],
            ['38', 'Santa Cruz de Tenerife', 'Canarias'],
            ['39', 'Cantabria', 'Cantabria'],
            ['40', 'Segovia', 'Castilla y León'],
            ['41', 'Sevilla', 'Andalucía'],
            ['42', 'Soria', 'Castilla y León'],
            ['43', 'Tarragona', 'Cataluña'],
            ['44', 'Teruel', 'Aragón'],
            ['45', 'Toledo', 'Castilla-La Mancha'],
            ['46', 'Valencia', 'Comunidad Valenciana'],
            ['47', 'Valladolid', 'Castilla y León'],
            ['48', 'Bizkaia', 'País Vasco'],
            ['49', 'Zamora', 'Castilla y León'],
            ['50', 'Zaragoza', 'Aragón'],
            ['51', 'Ceuta', 'Ceuta'],
            ['52', 'Melilla', 'Melilla'],
        ];

        Province::query()->upsert(
            array_map(fn (array $row) => [
                'code' => $row[0],
                'name' => $row[1],
                'region' => $row[2],
            ], $provinces),
            ['code'],
            ['name', 'region'],
        );
    }
}
