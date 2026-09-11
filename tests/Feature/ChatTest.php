<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Tests\TestCase;

class ChatTest extends TestCase
{
    public function test_a_guest_cannot_read_messages(): void
    {
        $this->get(route('messages.index'))->assertRedirect(route('login'));
    }

    public function test_writing_to_the_owner_opens_a_conversation(): void
    {
        $car = Car::factory()->create();
        $renter = User::factory()->create();

        $this->actingAs($renter)
            ->post(route('messages.start', $car))
            ->assertRedirect();

        $conversation = Conversation::query()->firstOrFail();

        $this->assertSame($car->id, $conversation->car_id);
        $this->assertSame($car->owner_id, $conversation->owner_id);
        $this->assertSame($renter->id, $conversation->renter_id);
    }

    public function test_writing_again_continues_the_same_conversation(): void
    {
        $car = Car::factory()->create();
        $renter = User::factory()->create();

        $this->actingAs($renter)->post(route('messages.start', $car));
        $this->actingAs($renter)->post(route('messages.start', $car));

        // Una sola por coche e interesado: si no, cada pregunta abriría un hilo
        // nuevo y la bandeja sería un basurero.
        $this->assertSame(1, Conversation::query()->count());
    }

    public function test_talking_to_the_owner_does_not_need_the_papers(): void
    {
        $car = Car::factory()->create();

        // Preguntar es gratis, y es lo que hace que alguien acabe reservando. Lo que
        // exige verificación es reservar y publicar.
        $this->actingAs(User::factory()->pendingReview()->create())
            ->post(route('messages.start', $car))
            ->assertRedirect();

        $this->assertSame(1, Conversation::query()->count());
    }

    public function test_nobody_writes_to_themselves(): void
    {
        $owner = User::factory()->create();
        $car = Car::factory()->for($owner, 'owner')->create();

        $this->actingAs($owner)->post(route('messages.start', $car))->assertForbidden();
    }

    public function test_a_hidden_car_does_not_take_new_conversations(): void
    {
        $car = Car::factory()->hidden()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('messages.start', $car))
            ->assertNotFound();
    }

    public function test_the_two_sides_can_write(): void
    {
        $conversation = Conversation::factory()->create();

        $this->actingAs($conversation->renter)
            ->post(route('messages.store', $conversation), ['body' => '¿Tiene silla de bebé?'])
            ->assertRedirect(route('messages.show', $conversation));

        $this->actingAs($conversation->owner)
            ->post(route('messages.store', $conversation), ['body' => 'Silla no, pero lleva Isofix.'])
            ->assertRedirect();

        $this->assertSame(2, $conversation->messages()->count());
    }

    public function test_nobody_else_reads_the_conversation(): void
    {
        $conversation = Conversation::factory()->create();
        Message::factory()->create(['conversation_id' => $conversation->id, 'body' => 'Te lo dejo en el garaje']);

        /*
         * Los mensajes llevan dónde se recoge un coche y a qué hora está uno en su
         * casa: tener el enlace no es tener permiso.
         */
        $this->actingAs(User::factory()->create())
            ->get(route('messages.show', $conversation))
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->post(route('messages.store', $conversation), ['body' => 'hola'])
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get(route('messages.poll', $conversation))
            ->assertForbidden();
    }

    public function test_an_empty_message_is_not_sent(): void
    {
        $conversation = Conversation::factory()->create();

        $this->actingAs($conversation->renter)
            ->post(route('messages.store', $conversation), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->assertSame(0, $conversation->messages()->count());
    }

    public function test_sending_a_message_moves_the_conversation_up_in_the_inbox(): void
    {
        $old = Conversation::factory()->create(['last_message_at' => now()->subDays(3)]);

        $fresh = Conversation::factory()->create([
            'renter_id' => $old->renter_id,
            'last_message_at' => null,
        ]);

        $this->actingAs($old->renter)->post(route('messages.store', $fresh), ['body' => 'Buenas']);

        $this->assertNotNull($fresh->refresh()->last_message_at);

        $order = $this->actingAs($old->renter)->get(route('messages.index'))->assertOk();

        $body = $order->getContent();
        $this->assertLessThan(
            strpos($body, route('messages.show', $old)),
            strpos($body, route('messages.show', $fresh)),
            'La conversación con el mensaje más nuevo tiene que salir antes.',
        );
    }

    public function test_a_conversation_without_messages_goes_to_the_bottom(): void
    {
        $renter = User::factory()->create();

        $talked = Conversation::factory()->create([
            'renter_id' => $renter->id,
            'last_message_at' => now()->subHour(),
        ]);

        $silent = Conversation::factory()->create(['renter_id' => $renter->id, 'last_message_at' => null]);

        // `order by last_message_at desc` a secas pondría los nulos arriba en
        // Postgres, que es justo al revés de lo que se quiere.
        $body = $this->actingAs($renter)->get(route('messages.index'))->assertOk()->getContent();

        $this->assertLessThan(
            strpos($body, route('messages.show', $silent)),
            strpos($body, route('messages.show', $talked)),
        );
    }

    public function test_opening_a_conversation_marks_the_others_messages_as_read(): void
    {
        $conversation = Conversation::factory()->create();

        $theirs = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->owner_id,
        ]);

        $mine = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->renter_id,
        ]);

        $this->actingAs($conversation->renter)->get(route('messages.show', $conversation))->assertOk();

        $this->assertNotNull($theirs->refresh()->read_at);
        // Los propios no: «leído» es que lo ha visto quien lo recibió.
        $this->assertNull($mine->refresh()->read_at);
    }

    public function test_the_header_counts_what_is_waiting(): void
    {
        $conversation = Conversation::factory()->create();

        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->owner_id,
        ]);

        $this->assertSame(3, $conversation->renter->unreadMessages());
        $this->assertSame(0, $conversation->owner->unreadMessages());

        $this->actingAs($conversation->renter)->get(route('messages.show', $conversation));

        $this->assertSame(0, $conversation->renter->refresh()->unreadMessages());
    }

    public function test_the_poll_only_brings_what_the_browser_does_not_have(): void
    {
        $conversation = Conversation::factory()->create();

        $first = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->owner_id,
            'body' => 'El primero',
        ]);

        $second = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->owner_id,
            'body' => 'El segundo',
        ]);

        $this->actingAs($conversation->renter)
            ->getJson(route('messages.poll', ['conversation' => $conversation, 'after' => $first->id]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.id', $second->id)
            ->assertJsonPath('messages.0.body', 'El segundo')
            ->assertJsonPath('messages.0.mine', false);
    }

    public function test_the_poll_says_which_messages_are_mine(): void
    {
        $conversation = Conversation::factory()->create();

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->renter_id,
        ]);

        $this->actingAs($conversation->renter)
            ->getJson(route('messages.poll', $conversation))
            ->assertOk()
            ->assertJsonPath('messages.0.mine', true);
    }

    public function test_the_message_body_is_escaped_on_the_page(): void
    {
        $conversation = Conversation::factory()->create();

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $conversation->owner_id,
            'body' => '<script>alert(1)</script>',
        ]);

        // Un mensaje es texto que escribe otra persona, y por ahí es por donde entra
        // un script ajeno.
        $this->actingAs($conversation->renter)
            ->get(route('messages.show', $conversation))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', escape: false)
            ->assertSee('&lt;script&gt;', escape: false);
    }

    public function test_the_inbox_only_shows_my_own_conversations(): void
    {
        $renter = User::factory()->create();

        $mine = Conversation::factory()->create(['renter_id' => $renter->id]);
        $theirs = Conversation::factory()->create();

        $body = $this->actingAs($renter)->get(route('messages.index'))->assertOk()->getContent();

        $this->assertStringContainsString(route('messages.show', $mine), $body);
        $this->assertStringNotContainsString(route('messages.show', $theirs), $body);
    }
}
