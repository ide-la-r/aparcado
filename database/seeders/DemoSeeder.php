<?php

namespace Database\Seeders;

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
 * Datos de ejemplo para poder mirar la aplicación desde el primer día: tres dueños
 * con planes distintos —para ver el orden del catálogo—, sus coches repartidos por
 * cuatro provincias, alguien que alquila y una conversación empezada.
 *
 * En local la contraseña de todos es «password». **En producción no**: son cinco
 * cuentas en una web abierta a internet, y con una contraseña que se adivina
 * cualquiera podría entrar como el dueño de un coche y borrarlo. Allí se les pone
 * una aleatoria que no sabe nadie, y quien quiera usar la aplicación se registra.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $features = Feature::query()->pluck('id', 'slug');

        $gente = User::factory()->state([
            'password' => app()->isProduction() ? Str::password(40) : 'password',
        ]);

        $ismael = $gente->create([
            'name' => 'Ismael',
            'surname' => 'De la Rosa',
            'email' => 'ismael@aparcado.test',
        ]);

        $premium = $gente->create([
            'name' => 'Lucía',
            'surname' => 'Ortega',
            'email' => 'lucia@aparcado.test',
        ]);

        $plus = $gente->create([
            'name' => 'Marcos',
            'surname' => 'Herrera',
            'email' => 'marcos@aparcado.test',
        ]);

        $sinPlan = $gente->create([
            'name' => 'Nuria',
            'surname' => 'Cano',
            'email' => 'nuria@aparcado.test',
        ]);

        // Quien alquila todavía no ha pasado la verificación: sirve para ver la
        // pantalla de «tenemos que mirar tus papeles».
        $inquilino = $gente->pendingReview()->create([
            'name' => 'Diego',
            'surname' => 'Salas',
            'email' => 'diego@aparcado.test',
        ]);

        $premium->subscriptions()->create([
            'plan' => Plan::Premium,
            'price_cents' => Plan::Premium->priceCents(),
            'starts_on' => Carbon::today()->subWeek(),
            'ends_on' => Carbon::today()->addWeeks(3),
        ]);

        $plus->subscriptions()->create([
            'plan' => Plan::Plus,
            'price_cents' => Plan::Plus->priceCents(),
            'starts_on' => Carbon::today()->subWeek(),
            'ends_on' => Carbon::today()->addWeeks(3),
        ]);

        $provinces = ['29' => 'Málaga', '28' => 'Madrid', '41' => 'Sevilla', '18' => 'Granada'];

        foreach ([$ismael, $premium, $plus, $sinPlan] as $index => $owner) {
            $code = array_keys($provinces)[$index];

            Car::factory()
                ->count(3)
                ->for($owner, 'owner')
                ->create(['province_code' => $code, 'city' => $provinces[$code]])
                ->each(function (Car $car) use ($features) {
                    $car->features()->sync(
                        $features->random(random_int(4, 9))->all()
                    );

                    foreach (range(1, 3) as $position) {
                        $car->photos()->create([
                            'path' => "demo/coche-{$car->id}-{$position}.jpg",
                            'position' => $position - 1,
                        ]);
                    }
                });
        }

        $coche = Car::query()->where('owner_id', $ismael->id)->firstOrFail();

        // Por las fábricas y no con `create()`: `car_id` y `sender_id` no son
        // asignables en masa a propósito —los pone el servidor, nunca un
        // formulario—, y una fábrica es la única que puede saltárselo.
        Booking::factory()
            ->for($coche)
            ->for($inquilino, 'renter')
            ->between(
                Carbon::today()->addWeek()->toDateString(),
                Carbon::today()->addWeek()->addDays(4)->toDateString(),
            )
            ->create([
                'price_cents_per_day' => $coche->price_cents,
                'total_cents' => $coche->price_cents * 5,
            ]);

        // Y otra sin pagar, en otro coche, para poder ver la pantalla de pago y la
        // diferencia entre «pendiente» y «confirmada» sin tocar la base de datos.
        $otro = Car::query()->where('owner_id', '!=', $inquilino->id)->latest('id')->firstOrFail();

        Booking::factory()
            ->for($otro)
            ->for($inquilino, 'renter')
            ->pending()
            ->between(
                Carbon::today()->addMonth()->toDateString(),
                Carbon::today()->addMonth()->addDays(2)->toDateString(),
            )
            ->create([
                'price_cents_per_day' => $otro->price_cents,
                'total_cents' => $otro->price_cents * 3,
            ]);

        $conversacion = Conversation::factory()->create([
            'car_id' => $coche->id,
            'owner_id' => $coche->owner_id,
            'renter_id' => $inquilino->id,
            'last_message_at' => now(),
        ]);

        $charla = [
            [$inquilino, '¡Hola! ¿El coche tiene silla de bebé?'],
            [$ismael, 'Buenas. Silla no, pero lleva Isofix y te la puedo dejar montada.'],
            [$inquilino, 'Perfecto, entonces lo reservo para la semana que viene.'],
        ];

        foreach ($charla as $index => [$quien, $texto]) {
            $cuando = now()->subMinutes(10 - $index * 3);

            Message::factory()->create([
                'conversation_id' => $conversacion->id,
                'sender_id' => $quien->id,
                'body' => $texto,
                'created_at' => $cuando,
                'updated_at' => $cuando,
            ]);
        }
    }
}
