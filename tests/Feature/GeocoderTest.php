<?php

namespace Tests\Feature;

use App\Services\Geocoding\Geocoder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeocoderTest extends TestCase
{
    public function test_it_reads_the_coordinates_in_the_order_geojson_puts_them(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response([
            // GeoJSON es [lon, lat], al revés de como se dice en voz alta.
            'features' => [['geometry' => ['coordinates' => [-4.4216044, 36.720606]]]],
        ])]);

        $found = $this->geocoder()->locate('Calle Larios 1', '29015', 'Málaga');

        $this->assertSame(36.720606, $found['lat']);
        $this->assertSame(-4.4216044, $found['lon']);
    }

    public function test_it_does_not_ask_photon_for_spanish(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => []])]);

        $this->geocoder()->locate('Calle Larios 1', '29015', 'Málaga');

        /*
         * Photon sólo admite unos pocos idiomas y el español no está: con `lang=es`
         * contesta 400 y no se geocodifica nada. Medido contra el servicio de verdad
         * el 11-09-2026, y este test es lo que impide que alguien lo «arregle»
         * añadiéndolo otra vez.
         */
        Http::assertSent(function (Request $request) {
            $this->assertStringNotContainsString('lang=', $request->url());
            $this->assertStringContainsString('limit=1', $request->url());

            return true;
        });
    }

    public function test_the_address_reaches_photon_as_one_query(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => []])]);

        $this->geocoder()->locate('Calle Larios 1', '29015', 'Málaga');

        Http::assertSent(fn (Request $request) => $request->data()['q'] === 'Calle Larios 1, 29015 Málaga, España');
    }

    public function test_an_answer_without_results_is_not_a_place(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response(['features' => []])]);

        $this->assertNull($this->geocoder()->locate('Calle de la Nada 0', '00000', 'Ninguna'));
    }

    public function test_a_broken_answer_is_not_a_place(): void
    {
        Http::fake(['photon.komoot.io/*' => Http::response('vaya', 500)]);

        $this->assertNull($this->geocoder()->locate('Calle Larios 1', '29015', 'Málaga'));
    }

    public function test_no_network_is_not_a_crash(): void
    {
        Http::fake(fn () => throw new ConnectionException('se ha ido la red'));

        // Lo que de verdad protege esto: `Http::get` lanza la excepción, y eso no lo
        // cubre comprobar `$response->failed()`.
        $this->assertNull($this->geocoder()->locate('Calle Larios 1', '29015', 'Málaga'));
    }

    private function geocoder(): Geocoder
    {
        return app(Geocoder::class);
    }
}
