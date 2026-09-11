<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Lo que pasa cuando alguien termina el TLS por ti.
 *
 * Los dos fallos que esto evita se midieron en el primer despliegue a Render, y
 * ninguno de los dos se ve en local: allí la petición ya llega por HTTP directo.
 */
class ProxyHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Una ruta de mentira que cuenta lo que la aplicación cree de la petición.
        Route::get('/_proxy', fn () => [
            'secure' => request()->isSecure(),
            'asset' => asset('build/app.css'),
            'ip' => request()->ip(),
        ]);
    }

    public function test_behind_the_proxy_the_links_come_out_in_https(): void
    {
        /*
         * Sin confiar en el proxy, el CSS se enlaza en http:// dentro de una página
         * https y el navegador lo bloquea por contenido mixto: la web se ve en HTML
         * pelado, sin un solo estilo.
         */
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-For' => '203.0.113.7',
        ])->getJson('/_proxy');

        $response->assertOk()->assertJson(['secure' => true]);

        $this->assertStringStartsWith('https://', $response->json('asset'));
    }

    public function test_behind_the_proxy_the_client_ip_is_the_real_one(): void
    {
        /*
         * Y si no, `ip()` devuelve la IP interna del proxy para todo el mundo: el
         * límite de seis intentos de entrada por IP metería a todos los usuarios en
         * el mismo cubo.
         */
        $this->withHeaders(['X-Forwarded-For' => '203.0.113.7'])
            ->getJson('/_proxy')
            ->assertOk()
            ->assertJson(['ip' => '203.0.113.7']);
    }

    public function test_without_the_headers_nothing_changes(): void
    {
        $this->getJson('/_proxy')->assertOk()->assertJson(['secure' => false]);
    }
}
