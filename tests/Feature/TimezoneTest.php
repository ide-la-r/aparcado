<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * La aplicación va en hora española, y esto lo sujeta.
 *
 * En UTC el chat enseñaba las 11:00 cuando eran las 13:00, y una hora mal en un
 * chat no parece un detalle: parece que la web está rota. Lo tapaba que
 * `render.yaml` ponía `APP_TIMEZONE`, que desde Laravel 11 no lee nadie.
 */
class TimezoneTest extends TestCase
{
    public function test_the_application_runs_on_spanish_time(): void
    {
        $this->assertSame('Europe/Madrid', date_default_timezone_get());
    }

    public function test_the_chat_prints_the_hour_it_is_in_spain(): void
    {
        // Las 09:30 en Londres son las 11:30 en España en julio.
        Carbon::setTestNow(Carbon::parse('2026-07-15T09:30:00Z'));

        $conversation = Conversation::factory()->create();
        $renter = $conversation->renter;

        Message::factory()->for($conversation)->create([
            'sender_id' => $conversation->owner_id,
            'body' => 'Te lo dejo con el depósito lleno.',
        ]);

        $this->actingAs($renter)
            ->get(route('messages.show', $conversation))
            ->assertOk()
            ->assertSee('11:30');
    }

    /**
     * Y el motivo de verdad para no dejarlo en UTC: entre medianoche y las dos de
     * la mañana hora española, en UTC «hoy» todavía es ayer. Las fechas de las
     * reservas se deciden con `Carbon::today()`, así que a esas horas la
     * disponibilidad saldría corrida un día entero.
     */
    public function test_today_is_today_in_spain_even_at_one_in_the_morning(): void
    {
        // La una de la madrugada del 16 en España son las 23:00 del 15 en UTC.
        Carbon::setTestNow(Carbon::parse('2026-07-15T23:00:00Z'));

        $this->assertSame('2026-07-16', Carbon::today()->toDateString());
    }
}
