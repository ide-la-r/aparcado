<?php

namespace App\Console\Commands;

use App\Support\DemoPhotos;
use Illuminate\Console\Command;

/**
 * Deja cada coche de ejemplo con las fotos que dice `config/demo_photos.php`.
 *
 * Lo llama el arranque del contenedor, como los datos de referencia, y por el
 * mismo motivo: es una reconciliación, no un apaño de una vez. Las fotos de los
 * ejemplos son parte del repositorio, así que lo que manda es el fichero de
 * configuración y la base de datos se pone al día sola en cada despliegue —igual
 * si se añade una foto nueva que si los ejemplos se sembraron, como pasó en
 * producción, cuando las fotos todavía no existían—.
 *
 * No puede pelearse con nadie: en producción las cuentas de ejemplo llevan una
 * contraseña aleatoria de cuarenta caracteres que no sabe nadie, así que a esos
 * coches no entra ninguna persona a cambiarles las fotos.
 */
class RefreshDemoPhotosCommand extends Command
{
    protected $signature = 'aparcado:refresh-demo-photos';

    protected $description = 'Vuelve a dejar cada coche de ejemplo con sus fotos';

    public function handle(): int
    {
        $cars = DemoPhotos::refresh();

        $this->info("Fotos repuestas en {$cars} coches de ejemplo.");

        return self::SUCCESS;
    }
}
