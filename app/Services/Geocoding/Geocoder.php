<?php

namespace App\Services\Geocoding;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pasa una dirección a coordenadas con Photon, el buscador de OpenStreetMap: sin
 * clave, sin tarjeta y sin registro.
 *
 * Nunca hace fallar el guardado. Si no contesta, el coche se guarda sin
 * coordenadas y su ficha sale sin mapa: perder el mapa es un detalle, perder el
 * anuncio que alguien acaba de escribir no lo es.
 */
class Geocoder
{
    private const ENDPOINT = 'https://photon.komoot.io/api';

    private const TIMEOUT = 6;

    /** @return array{lat: float, lon: float}|null */
    public function locate(string $address, string $postalCode, string $city): ?array
    {
        $query = trim("{$address}, {$postalCode} {$city}, España");

        try {
            /*
             * Sin `lang`, y no por descuido: Photon sólo acepta unos pocos idiomas y
             * el español no es uno de ellos, así que `lang=es` devuelve un 400 y no
             * se geocodifica nada. Medido contra el servicio el 11-09-2026. Da
             * igual, porque de aquí sólo se sacan las coordenadas.
             */
            $response = Http::timeout(self::TIMEOUT)
                ->acceptJson()
                ->get(self::ENDPOINT, ['q' => $query, 'limit' => 1]);
        } catch (Throwable $exception) {
            /*
             * `Http::get` lanza `ConnectionException` cuando no hay red o se agota
             * el tiempo, y eso NO lo cubre comprobar `$response->failed()`. Sin
             * este catch, un corte de red convierte «guardar mi coche» en un 500.
             */
            Log::warning('Photon no ha contestado', ['query' => $query, 'error' => $exception->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            return null;
        }

        // GeoJSON: las coordenadas van [lon, lat], en ese orden.
        $coordinates = $response->json('features.0.geometry.coordinates');

        if (! is_array($coordinates) || count($coordinates) < 2) {
            return null;
        }

        return ['lat' => (float) $coordinates[1], 'lon' => (float) $coordinates[0]];
    }
}
