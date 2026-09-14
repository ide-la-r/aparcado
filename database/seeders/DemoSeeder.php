<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\Plan;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Conversation;
use App\Models\Feature;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Datos de ejemplo para poder mirar la aplicación desde el primer día: cuatro
 * dueños con planes distintos —para ver el orden del catálogo—, sus coches
 * repartidos por cuatro provincias, alguien que alquila y una conversación
 * empezada.
 *
 * **Escrito a mano y sin fábricas, a propósito.** Las fábricas llaman a `fake()`,
 * y Faker es una dependencia de desarrollo: la imagen de producción se construye
 * con `--no-dev`, así que allí no existe y sembrar acababa en un 500. Además, los
 * datos inventados daban ciudades que no están en su provincia y descripciones en
 * latín, que para un catálogo que se enseña es peor que no tener nada.
 *
 * En local la contraseña de todos es «password». **En producción no**: son cinco
 * cuentas en una web abierta a internet, y con una contraseña que se adivina
 * cualquiera podría entrar como el dueño de un coche y borrarlo. Allí se les pone
 * una aleatoria que no sabe nadie, y quien quiera usar la aplicación se registra.
 */
class DemoSeeder extends Seeder
{
    /**
     * Los coches, tres por dueño. Las coordenadas son del centro de cada ciudad:
     * la ficha sólo enseña la zona, y las redondea a dos decimales antes de
     * dibujar el mapa.
     */
    private const CARS = [
        // Ismael · Málaga
        ['1834 KLM', 'Seat', 'Ibiza', 2019, 78_400, 'Gasolina', 'Manual', 'Urbano', 'Blanco', 5, 5, 110, true, 3200, 'Málaga', '29010', 36.7213, -4.4214, 'Calle Armengual de la Mota', 'Lo uso sólo para ir al trabajo, así que los fines de semana está parado. Aparca fácil y tiene la ITV recién pasada.'],
        ['4471 BCD', 'Volkswagen', 'Golf', 2021, 41_200, 'Diésel', 'Automático', 'Compacto', 'Gris', 5, 5, 150, true, 4800, 'Torremolinos', '29620', 36.6205, -4.4999, 'Avenida Palma de Mallorca', 'Cómodo para carretera y gasta muy poco. Lo tengo en un garaje, así que siempre sale limpio.'],
        ['9126 FGH', 'Dacia', 'Duster', 2018, 112_800, 'Gasolina', 'Manual', 'Todoterreno', 'Azul', 5, 5, 115, false, 3500, 'Ronda', '29400', 36.7420, -5.1665, 'Calle Sevilla', 'Va bien por caminos y sube la sierra sin quejarse. Si vais a la sierra de Grazalema es el coche.'],

        // Lucía · Madrid (Premium)
        ['2287 JKP', 'Toyota', 'Corolla', 2022, 33_500, 'Híbrido', 'Automático', 'Berlina', 'Plata', 5, 5, 122, true, 5200, 'Madrid', '28012', 40.4168, -3.7038, 'Calle de Embajadores', 'Híbrido de verdad: por ciudad casi no gasta. Tiene etiqueta ECO, así que entra en Madrid Central sin problema.'],
        ['6693 LMN', 'Renault', 'Clio', 2020, 58_900, 'Gasolina', 'Manual', 'Urbano', 'Rojo', 5, 5, 100, true, 2900, 'Alcalá de Henares', '28801', 40.4818, -3.3644, 'Calle Mayor', 'Pequeño y muy fácil de aparcar. Ideal si os movéis por el centro.'],
        ['5518 PQR', 'Volkswagen', 'Tiguan', 2021, 47_300, 'Diésel', 'Automático', 'SUV', 'Negro', 5, 5, 150, true, 6500, 'Getafe', '28901', 40.3057, -3.7329, 'Avenida de las Ciudades', 'Espacioso para viajes largos con equipaje. Lleva enganche para remolque por si hace falta.'],

        // Marcos · Sevilla (Plus)
        ['3342 RST', 'Peugeot', '208', 2021, 39_600, 'Eléctrico', 'Automático', 'Urbano', 'Blanco', 5, 5, 136, true, 4200, 'Sevilla', '41004', 37.3891, -5.9845, 'Calle San Fernando', 'Eléctrico, unos 340 km reales. Se carga en cualquier punto de la ciudad y no paga zona azul.'],
        ['7705 TUV', 'Citroën', 'Berlingo', 2019, 96_100, 'Diésel', 'Manual', 'Furgoneta', 'Blanco', 5, 5, 130, false, 4000, 'Dos Hermanas', '41700', 37.2836, -5.9223, 'Avenida de Andalucía', 'Cabe de todo. La uso para mudanzas y para llevar las bicis.'],
        ['1159 VWX', 'Seat', 'León', 2020, 64_700, 'Gasolina', 'Manual', 'Compacto', 'Gris', 5, 5, 130, true, 3800, 'Utrera', '41710', 37.1853, -5.7810, 'Calle Ancha', 'Lleva bien la carretera y el maletero es grande para lo que parece.'],

        // Nuria · Granada
        ['8820 XYZ', 'Fiat', '500', 2018, 71_300, 'Gasolina', 'Manual', 'Urbano', 'Amarillo', 4, 3, 69, false, 2400, 'Granada', '18009', 37.1773, -3.5986, 'Calle Recogidas', 'Perfecto para el casco antiguo, donde no cabe nada más grande. Dos plazas atrás justitas.'],
        ['4408 ZAB', 'Toyota', 'RAV4', 2022, 28_400, 'Híbrido', 'Automático', 'SUV', 'Azul', 5, 5, 222, true, 7200, 'Motril', '18600', 36.7500, -3.5183, 'Avenida de Salobreña', 'Grande, cómodo y con tracción a las cuatro ruedas. Para la playa o para subir a Sierra Nevada.'],
        ['6674 BCE', 'Renault', 'Mégane', 2019, 88_500, 'Diésel', 'Manual', 'Familiar', 'Gris', 5, 5, 115, true, 3300, 'Armilla', '18100', 37.1435, -3.6167, 'Calle Real', 'Familiar de maletero enorme. Con silla de bebé y dos maletas sobra sitio.'],
    ];

    public function run(): void
    {
        $features = Feature::query()->orderBy('position')->pluck('id')->all();

        // En local la misma para todos, para poder entrar; en producción una que
        // no sabe nadie.
        $password = app()->isProduction() ? Str::password(40) : 'password';

        $owners = [
            $this->person('Ismael', 'De la Rosa', 'ismael@aparcado.test', '600112233', $password),
            $this->person('Lucía', 'Ortega', 'lucia@aparcado.test', '600223344', $password),
            $this->person('Marcos', 'Herrera', 'marcos@aparcado.test', '600334455', $password),
            $this->person('Nuria', 'Cano', 'nuria@aparcado.test', '600445566', $password),
        ];

        // Quien alquila todavía no ha pasado la verificación: sirve para ver la
        // pantalla de «tenemos que mirar tus papeles».
        $renter = $this->person('Diego', 'Salas', 'diego@aparcado.test', '600556677', $password, verified: false);

        $this->subscribe($owners[1], Plan::Premium);
        $this->subscribe($owners[2], Plan::Plus);

        $provinces = ['29', '28', '41', '18'];
        $cars = [];

        foreach (self::CARS as $index => $row) {
            [$plate, $brand, $model, $year, $km, $fuel, $gearbox, $body, $colour,
                $seats, $doors, $power, $insured, $price, $city, $postalCode,
                $lat, $lon, $address, $description] = $row;

            $car = $owners[intdiv($index, 3)]->cars()->create([
                'plate' => $plate,
                'brand' => $brand,
                'model' => $model,
                'registration_year' => $year,
                'kilometres' => $km,
                'fuel' => $fuel,
                'transmission' => $gearbox,
                'body_type' => $body,
                'colour' => $colour,
                'seats' => $seats,
                'doors' => $doors,
                'power_hp' => $power,
                'has_insurance' => $insured,
                'price_cents' => $price,
                'description' => $description,
                'address' => $address,
                'city' => $city,
                'province_code' => $provinces[intdiv($index, 3)],
                'postal_code' => $postalCode,
                'latitude' => $lat,
                'longitude' => $lon,
                'parking_type' => $index % 2 === 0 ? 'Garaje privado' : 'Calle',
                'published' => true,
            ]);

            // Un puñado distinto para cada coche, pero siempre el mismo: unos datos
            // de ejemplo que cambian en cada siembra no sirven para comparar nada.
            $car->features()->sync(array_slice($features, $index % 6, 5));

            foreach (range(1, 3) as $position) {
                $car->photos()->create([
                    'path' => "demo/coche-{$car->id}-{$position}.jpg",
                    'position' => $position - 1,
                ]);
            }

            $cars[] = $car;
        }

        // Una pagada, que ocupa sus fechas.
        $this->book($cars[0], $renter, Carbon::today()->addWeek(), 5, BookingStatus::Confirmed);

        // Y otra sin pagar, en otro coche, para ver la pantalla de pago y la
        // diferencia entre «pendiente» y «confirmada» sin tocar la base de datos.
        $this->book($cars[7], $renter, Carbon::today()->addMonth(), 3, BookingStatus::Pending);

        $this->chat($cars[0], $renter);
    }

    private function person(string $name, string $surname, string $email, string $phone, string $password, bool $verified = true): User
    {
        $user = User::create([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
            'birthdate' => '1994-06-12',
            'document_type' => 'DNI',
            'document_number' => Str::upper(Str::random(8)).'X',
        ]);

        // `verified_at` y `email_verified_at` se quedan fuera de la asignación en
        // masa a propósito: no los pone un formulario.
        $user->email_verified_at = now();
        $user->verified_at = $verified ? now() : null;
        $user->save();

        return $user;
    }

    private function subscribe(User $user, Plan $plan): void
    {
        $user->subscriptions()->create([
            'plan' => $plan,
            'price_cents' => $plan->priceCents(),
            'starts_on' => Carbon::today()->subWeek(),
            'ends_on' => Carbon::today()->addWeeks(3),
        ]);
    }

    private function book(Car $car, User $renter, Carbon $from, int $days, BookingStatus $status): void
    {
        $booking = new Booking([
            'starts_on' => $from->toDateString(),
            // El día de fin cuenta, así que cinco días son cuatro noches después.
            'ends_on' => $from->copy()->addDays($days - 1)->toDateString(),
            'days' => $days,
            'price_cents_per_day' => $car->price_cents,
            'total_cents' => $car->price_cents * $days,
            'status' => $status,
        ]);

        $booking->car()->associate($car);
        $booking->renter()->associate($renter);

        if ($status === BookingStatus::Confirmed) {
            $booking->confirmed_at = now();
        }

        $booking->save();
    }

    private function chat(Car $car, User $renter): void
    {
        $conversation = new Conversation;
        $conversation->car()->associate($car);
        $conversation->owner()->associate($car->owner_id);
        $conversation->renter()->associate($renter);
        $conversation->save();

        $charla = [
            [$renter->id, '¡Hola! ¿El coche tiene silla de bebé?'],
            [$car->owner_id, 'Buenas. Silla no, pero lleva Isofix y te la puedo dejar montada.'],
            [$renter->id, 'Perfecto, entonces lo reservo para la semana que viene.'],
        ];

        foreach ($charla as $index => [$senderId, $text]) {
            $when = now()->subMinutes(10 - $index * 3);

            $message = new Message(['body' => $text]);
            $message->conversation()->associate($conversation);
            $message->sender()->associate($senderId);
            $message->created_at = $when;
            $message->updated_at = $when;
            $message->save();
        }

        $conversation->forceFill(['last_message_at' => now()])->save();
    }
}
