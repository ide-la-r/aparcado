<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MyCarsTest extends TestCase
{
    /** Lo que contesta Photon en este test; a null, se hace el muerto. */
    private ?array $photon = ['lat' => 36.7213, 'lon' => -4.4214];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        /*
         * Un solo doble para todo el fichero, que lee esta propiedad cuando le
         * llaman. Registrar un segundo `Http::fake()` a mitad de un test no
         * sustituye al primero —se apila, y el orden en que gana no es evidente—,
         * así que lo que cambia es la respuesta, no el doble.
         */
        Http::fake(['photon.komoot.io/*' => function () {
            if ($this->photon === null) {
                throw new ConnectionException('se ha ido la red');
            }

            return Http::response([
                // GeoJSON: las coordenadas van [lon, lat].
                'features' => [['geometry' => ['coordinates' => [$this->photon['lon'], $this->photon['lat']]]]],
            ]);
        }]);
    }

    public function test_a_guest_is_sent_to_the_login_form(): void
    {
        $this->get(route('my-cars.index'))->assertRedirect(route('login'));
    }

    public function test_someone_without_papers_is_sent_to_their_profile(): void
    {
        $user = User::factory()->pendingReview()->create();

        $this->actingAs($user)
            ->get(route('my-cars.create'))
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('warning');
    }

    public function test_someone_waiting_for_the_review_is_told_so(): void
    {
        $user = User::factory()->pendingReview()->create([
            'document_photo_path' => 'documents/dni.jpg',
            'licence_photo_path' => 'licences/carne.jpg',
        ]);

        $this->actingAs($user)->get(route('my-cars.create'))->assertRedirect(route('profile.show'));

        $this->assertStringContainsString('Estamos mirando tus papeles', session('warning'));
    }

    public function test_the_list_is_there_even_without_papers(): void
    {
        // La lista no publica nada, así que no hay razón para esconderla: lo que
        // exige la verificación es publicar.
        $this->actingAs(User::factory()->pendingReview()->create())
            ->get(route('my-cars.index'))
            ->assertOk()
            ->assertSee('Todavía no tienes ninguno publicado.');
    }

    public function test_someone_verified_can_publish_a_car(): void
    {
        $user = User::factory()->create();
        $gps = Feature::query()->where('slug', 'gps')->firstOrFail();

        $this->actingAs($user)
            ->post(route('my-cars.store'), $this->validData([
                'features' => [$gps->id],
                'photos' => [UploadedFile::fake()->image('coche.jpg')],
            ]))
            ->assertRedirect(route('my-cars.index'));

        $car = Car::query()->where('owner_id', $user->id)->firstOrFail();

        $this->assertSame('Seat Ibiza', $car->title());
        $this->assertSame(3550, $car->price_cents);
        $this->assertEqualsCanonicalizing([$gps->id], $car->features->pluck('id')->all());
        $this->assertCount(1, $car->photos);
        Storage::disk('public')->assertExists($car->photos->first()->path);
    }

    public function test_the_plate_is_tidied_up_before_it_is_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('my-cars.store'), $this->validData(['plate' => '  1234   abc ']));

        // Si no, «1234 abc» y «1234  ABC» son dos coches para la regla de unicidad
        // y el mismo coche en la calle.
        $this->assertSame('1234 ABC', Car::query()->where('owner_id', $user->id)->value('plate'));
    }

    public function test_the_price_is_read_with_a_comma_and_with_a_dot(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('my-cars.store'), $this->validData(['price' => '42,75']));
        $this->assertSame(4275, Car::query()->where('owner_id', $user->id)->value('price_cents'));

        $this->actingAs($user)->post(route('my-cars.store'), $this->validData([
            'plate' => '5678 XYZ',
            'price' => '42.75',
        ]));

        $this->assertSame(2, Car::query()->where('owner_id', $user->id)->count());
    }

    public function test_a_price_that_is_not_a_number_says_so(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('my-cars.store'), $this->validData(['price' => 'lo que sea']))
            ->assertSessionHasErrors(['price_cents' => 'El precio tiene que ser un número, como 35,50.']);

        $this->assertDatabaseCount('cars', 0);
    }

    public function test_two_cars_cannot_share_a_plate(): void
    {
        Car::factory()->create(['plate' => '1234 ABC']);

        $this->actingAs(User::factory()->create())
            ->post(route('my-cars.store'), $this->validData(['plate' => '1234 ABC']))
            ->assertSessionHasErrors(['plate' => 'Ya hay un coche publicado con esa matrícula.']);
    }

    public function test_the_address_becomes_coordinates(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('my-cars.store'), $this->validData());

        $car = Car::query()->where('owner_id', $user->id)->firstOrFail();

        $this->assertSame('36.7213000', $car->latitude);
        $this->assertSame('-4.4214000', $car->longitude);
    }

    public function test_the_car_is_saved_even_if_the_geocoder_is_down(): void
    {
        $this->photonIsDown();

        $user = User::factory()->create();

        // Perder el mapa es un detalle; perder el anuncio que alguien acaba de
        // escribir, no. Antes esto habría sido un 500 en la cara del dueño.
        $this->actingAs($user)
            ->post(route('my-cars.store'), $this->validData())
            ->assertRedirect(route('my-cars.index'));

        $car = Car::query()->where('owner_id', $user->id)->firstOrFail();

        $this->assertNull($car->latitude);
        $this->assertNull($car->longitude);
    }

    public function test_editing_the_price_does_not_ask_the_geocoder_again(): void
    {
        $user = User::factory()->create();
        $car = Car::factory()->for($user, 'owner')->create([
            'address' => 'Calle Larios 1',
            'postal_code' => '29015',
            'city' => 'Málaga',
            'province_code' => '29',
        ]);

        $this->actingAs($user)->patch(route('my-cars.update', $car), $this->validData([
            'plate' => $car->plate,
            'address' => 'Calle Larios 1',
            'postal_code' => '29015',
            'city' => 'Málaga',
            'province_code' => '29',
            'price' => '99,00',
        ]))->assertRedirect();

        // Una respuesta peor de Photon podría mover un coche que estaba bien puesto.
        Http::assertNothingSent();
        $this->assertSame(9900, $car->refresh()->price_cents);
    }

    public function test_moving_the_car_asks_the_geocoder_again(): void
    {
        $user = User::factory()->create();
        $car = Car::factory()->for($user, 'owner')->create(['city' => 'Málaga', 'province_code' => '29']);

        $this->photonReturns(40.4168, -3.7038);

        $this->actingAs($user)->patch(route('my-cars.update', $car), $this->validData([
            'plate' => $car->plate,
            'city' => 'Madrid',
            'province_code' => '28',
            'postal_code' => '28013',
        ]))->assertRedirect();

        $this->assertSame('40.4168000', $car->refresh()->latitude);
    }

    public function test_nobody_can_edit_somebody_elses_car(): void
    {
        $car = Car::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('my-cars.edit', $car))->assertForbidden();
        $this->actingAs($stranger)->patch(route('my-cars.update', $car), $this->validData())->assertForbidden();
        $this->actingAs($stranger)->delete(route('my-cars.destroy', $car))->assertForbidden();
    }

    public function test_the_owner_can_hide_the_car_without_deleting_it(): void
    {
        $user = User::factory()->create();
        $car = Car::factory()->for($user, 'owner')->create();

        $this->actingAs($user)->patch(route('my-cars.update', $car), $this->validData([
            'plate' => $car->plate,
            'published' => null,
        ]))->assertRedirect();

        $this->assertFalse($car->refresh()->published);
        $this->assertNotSoftDeleted($car);
    }

    public function test_retiring_a_car_keeps_the_bookings_it_had(): void
    {
        $user = User::factory()->create();
        $car = Car::factory()->for($user, 'owner')->create();
        $booking = Booking::factory()->for($car)->create();

        $this->actingAs($user)->delete(route('my-cars.destroy', $car))->assertRedirect(route('my-cars.index'));

        $this->assertSoftDeleted($car);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    public function test_a_retired_plate_can_be_used_again(): void
    {
        $old = Car::factory()->create(['plate' => '1234 ABC']);
        $old->delete();

        // El coche viejo ya no está en el catálogo, así que su matrícula no puede
        // bloquear para siempre al que la lleve ahora.
        $this->actingAs(User::factory()->create())
            ->post(route('my-cars.store'), $this->validData(['plate' => '1234 ABC']))
            ->assertSessionHasNoErrors();
    }

    public function test_the_owner_can_take_a_photo_off(): void
    {
        $user = User::factory()->create();
        $car = Car::factory()->for($user, 'owner')->create();
        $photo = $car->photos()->create(['path' => 'cars/una.jpg', 'position' => 0]);

        Storage::disk('public')->put('cars/una.jpg', 'contenido');

        $this->actingAs($user)
            ->delete(route('my-cars.photos.destroy', [$car, $photo]))
            ->assertRedirect();

        $this->assertDatabaseMissing('car_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing('cars/una.jpg');
    }

    public function test_a_photo_of_another_car_is_not_found(): void
    {
        $user = User::factory()->create();
        $mine = Car::factory()->for($user, 'owner')->create();
        $theirs = Car::factory()->create();
        $photo = $theirs->photos()->create(['path' => 'cars/suya.jpg', 'position' => 0]);

        // Con el `id` de mi coche y el de una foto ajena: la ruta tiene que negarse.
        $this->actingAs($user)
            ->delete(route('my-cars.photos.destroy', [$mine, $photo]))
            ->assertNotFound();

        $this->assertDatabaseHas('car_photos', ['id' => $photo->id]);
    }

    public function test_the_new_photos_go_after_the_ones_already_there(): void
    {
        $user = User::factory()->create();
        $car = Car::factory()->for($user, 'owner')->create();
        $cover = $car->photos()->create(['path' => 'cars/portada.jpg', 'position' => 0]);

        $this->actingAs($user)->patch(route('my-cars.update', $car), $this->validData([
            'plate' => $car->plate,
            'photos' => [UploadedFile::fake()->image('otra.jpg')],
        ]));

        // Subir una foto más no cambia la portada del catálogo sin avisar.
        $this->assertSame($cover->id, $car->refresh()->coverPhoto()->id);
        $this->assertCount(2, $car->photos);
    }

    public function test_the_list_only_shows_my_own_cars(): void
    {
        $user = User::factory()->create();
        Car::factory()->for($user, 'owner')->create(['brand' => 'Seat', 'model' => 'León']);
        Car::factory()->create(['brand' => 'Fiat', 'model' => 'Panda']);

        $this->actingAs($user)
            ->get(route('my-cars.index'))
            ->assertOk()
            ->assertSee('Seat León')
            ->assertDontSee('Fiat Panda');
    }

    private function photonReturns(float $lat, float $lon): void
    {
        $this->photon = ['lat' => $lat, 'lon' => $lon];
    }

    private function photonIsDown(): void
    {
        $this->photon = null;
    }

    private function validData(array $overrides = []): array
    {
        return [
            'plate' => '1234 ABC',
            'brand' => 'Seat',
            'model' => 'Ibiza',
            'registration_year' => 2019,
            'kilometres' => 84000,
            'fuel' => 'Gasolina',
            'transmission' => 'Manual',
            'body_type' => 'Compacto',
            'colour' => 'Blanco',
            'seats' => 5,
            'doors' => 5,
            'power_hp' => 110,
            'has_insurance' => '1',
            'price' => '35,50',
            'description' => 'Recién pasada la ITV.',
            'address' => 'Calle Larios 1',
            'city' => 'Málaga',
            'province_code' => '29',
            'postal_code' => '29015',
            'parking_type' => 'Calle',
            'published' => '1',
            ...$overrides,
        ];
    }
}
