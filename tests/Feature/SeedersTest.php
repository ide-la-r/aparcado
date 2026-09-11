<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Conversation;
use App\Models\Feature;
use App\Models\Message;
use App\Models\Province;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    public function test_the_fifty_two_provinces_are_seeded_with_their_ine_code(): void
    {
        $this->assertSame(52, Province::query()->count());
        $this->assertSame('Madrid', Province::query()->find('28')->name);
        $this->assertSame('Andalucía', Province::query()->find('29')->region);
    }

    public function test_the_extras_catalogue_keeps_the_twenty_of_the_original_project(): void
    {
        $this->assertSame(20, Feature::query()->count());

        // Lo que el TFG llamaba «android_carplay» es Android Auto.
        $this->assertTrue(Feature::query()->where('slug', 'android-auto')->exists());
        $this->assertFalse(Feature::query()->where('slug', 'android-carplay')->exists());
    }

    public function test_every_extra_belongs_to_a_group_that_exists(): void
    {
        $groups = array_keys(config('aparcado.feature_groups'));

        foreach (Feature::query()->get() as $feature) {
            $this->assertContains($feature->group, $groups, "El extra «{$feature->slug}» tiene un grupo que no existe.");
        }
    }

    public function test_seeding_the_reference_data_twice_does_not_duplicate_it(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame(52, Province::query()->count());
        $this->assertSame(20, Feature::query()->count());
    }

    public function test_the_demo_data_leaves_something_to_look_at(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertSame(5, User::query()->count());
        // Cuatro dueños con tres coches cada uno, y ni uno de más: las fábricas
        // perezosas no dejan coches huérfanos por detrás.
        $this->assertSame(12, Car::query()->count());
        $this->assertSame(1, Conversation::query()->count());
        $this->assertSame(3, Message::query()->count());

        $this->assertSame(
            ['18', '28', '29', '41'],
            Car::query()->distinct()->orderBy('province_code')->pluck('province_code')->all(),
        );
    }

    public function test_every_demo_car_has_photos_and_extras(): void
    {
        $this->seed(DemoSeeder::class);

        foreach (Car::query()->with(['photos', 'features'])->get() as $car) {
            $this->assertCount(3, $car->photos, "El coche {$car->id} no tiene fotos.");
            $this->assertGreaterThanOrEqual(4, $car->features->count(), "El coche {$car->id} no tiene extras.");
        }
    }
}
