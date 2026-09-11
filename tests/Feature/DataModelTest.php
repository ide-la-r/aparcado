<?php

namespace Tests\Feature;

use App\Enums\Plan;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Conversation;
use App\Models\Feature;
use App\Models\Message;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DataModelTest extends TestCase
{
    public function test_a_car_belongs_to_its_owner_and_province(): void
    {
        $car = Car::factory()->create(['province_code' => '29']);

        $this->assertTrue($car->owner->exists);
        $this->assertSame('Málaga', $car->province->name);
    }

    public function test_the_photos_of_a_car_come_out_in_the_order_the_owner_chose(): void
    {
        $car = Car::factory()->create();

        $car->photos()->create(['path' => 'segunda.jpg', 'position' => 1]);
        $car->photos()->create(['path' => 'portada.jpg', 'position' => 0]);

        $this->assertSame('portada.jpg', $car->photos()->first()->path);
        $this->assertSame('portada.jpg', $car->refresh()->coverPhoto()->path);
    }

    public function test_the_extras_of_a_car_are_a_relation_and_not_twenty_columns(): void
    {
        $car = Car::factory()->create();
        $gps = Feature::query()->where('slug', 'gps')->firstOrFail();
        $isofix = Feature::query()->where('slug', 'isofix')->firstOrFail();

        $car->features()->attach([$gps->id, $isofix->id]);

        $this->assertEqualsCanonicalizing(
            ['gps', 'isofix'],
            $car->features->pluck('slug')->all(),
        );
    }

    public function test_deleting_a_car_keeps_the_bookings_it_already_had(): void
    {
        $car = Car::factory()->create();
        $booking = Booking::factory()->for($car)->create();

        $car->delete();

        $this->assertSoftDeleted($car);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'car_id' => $car->id]);
    }

    public function test_a_subscription_stops_counting_once_it_expires(): void
    {
        $user = User::factory()->create();

        Subscription::factory()->for($user)->expired()->create();

        $this->assertNull($user->load('subscriptions')->activeSubscription());
    }

    public function test_the_active_subscription_is_the_one_running_today(): void
    {
        $user = User::factory()->create();

        Subscription::factory()->for($user)->expired()->create();
        Subscription::factory()->for($user)->plan(Plan::Premium)->create();

        $active = $user->load('subscriptions')->activeSubscription();

        $this->assertSame(Plan::Premium, $active->plan);
        $this->assertSame(1999, $active->price_cents);
    }

    public function test_cancelling_a_subscription_keeps_it_alive_until_its_end_date(): void
    {
        $subscription = Subscription::factory()->create(['cancelled_at' => now()]);

        // Lo pagado es del usuario hasta el último día: cancelar sólo impide que se
        // renueve.
        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->isActive(Carbon::today()->addMonths(2)));
    }

    public function test_a_conversation_knows_who_is_talking_to_whom(): void
    {
        $conversation = Conversation::factory()->create();
        $owner = $conversation->owner;
        $renter = $conversation->renter;

        $this->assertTrue($conversation->includes($owner));
        $this->assertTrue($conversation->includes($renter));
        $this->assertFalse($conversation->includes(User::factory()->create()));

        $this->assertSame($renter->id, $conversation->counterpartFor($owner)->id);
        $this->assertSame($owner->id, $conversation->counterpartFor($renter)->id);
    }

    public function test_a_conversation_is_created_for_the_car_without_inventing_another_one(): void
    {
        $car = Car::factory()->create();

        $conversation = Conversation::factory()->create(['car_id' => $car->id]);

        // La fábrica es perezosa: si le dan el coche, no crea uno suelto por detrás.
        $this->assertSame($car->owner_id, $conversation->owner_id);
        $this->assertSame(1, Car::query()->count());
    }

    public function test_the_messages_of_a_conversation_hang_from_it(): void
    {
        $conversation = Conversation::factory()->create();

        Message::factory()->count(3)->create(['conversation_id' => $conversation->id]);

        $this->assertCount(3, $conversation->messages);
        $this->assertSame(
            $conversation->renter_id,
            $conversation->messages->first()->sender_id,
        );
    }

    public function test_a_booking_keeps_the_price_of_the_day_it_was_made(): void
    {
        $car = Car::factory()->create(['price_cents' => 3500]);
        $booking = Booking::factory()->for($car)->between('2026-10-01', '2026-10-03')->create([
            'price_cents_per_day' => 3500,
            'total_cents' => 10500,
        ]);

        $car->update(['price_cents' => 9000]);

        $this->assertSame(3500, $booking->refresh()->price_cents_per_day);
        $this->assertSame(10500, $booking->total_cents);
        $this->assertSame(3, $booking->days);
    }
}
