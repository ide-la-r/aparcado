<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Chat\Conversations;
use App\Support\ChatTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(private readonly Conversations $conversations) {}

    public function index(Request $request): View
    {
        $conversations = Conversation::query()
            ->of($request->user())
            ->with(['car.photos', 'owner', 'renter', 'lastMessage'])
            ->withCount(['messages as unread_count' => function ($messages) use ($request) {
                $messages->where('sender_id', '!=', $request->user()->id)->whereNull('read_at');
            }])
            // Las que no tienen ningún mensaje van al final, no arriba.
            ->orderByRaw('last_message_at is null, last_message_at desc')
            ->get();

        return view('messages.index', ['conversations' => $conversations]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorize('view', $conversation);

        $this->conversations->markAsRead($conversation, $request->user());

        $conversation->load(['car.owner', 'car.photos', 'owner', 'renter']);

        return view('messages.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages()->with('sender')->orderBy('id')->get(),
            'counterpart' => $conversation->counterpartFor($request->user()),
        ]);
    }

    /** Escribir al dueño desde la ficha del coche. */
    public function start(Request $request, Car $car): RedirectResponse
    {
        abort_if($car->owner_id === $request->user()->id, 403);
        abort_unless($car->published, 404);

        $conversation = $this->conversations->openFor($car, $request->user());

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Enviar.
     *
     * Responde en JSON cuando quien llama lo pide, que es lo que permite escribir
     * sin recargar la página entera: en un chat, perder el sitio del hilo y el foco
     * del cuadro de texto con cada mensaje se nota muchísimo. El formulario de
     * siempre sigue estando ahí para quien tenga el JavaScript apagado, y por ese
     * camino la respuesta sigue siendo la redirección.
     */
    public function store(Request $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $this->authorize('send', $conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [], ['body' => 'el mensaje']);

        $message = $this->conversations->send($conversation, $request->user(), $validated['body']);

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->payload($message, $request->user())]);
        }

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Lo que va llegando, para que la conversación no haya que recargarla a mano.
     * Devuelve sólo lo posterior al último mensaje que ya tiene el navegador, así
     * que una consulta cada pocos segundos es una consulta por índice y nada más.
     */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $request->validate(['after' => ['nullable', 'integer', 'min:0']]);

        $messages = $conversation->messages()
            ->where('id', '>', (int) $request->integer('after'))
            ->orderBy('id')
            ->get();

        // Lo que llega por aquí se acaba de ver, así que cuenta como leído.
        $this->conversations->markAsRead($conversation, $request->user());

        return response()->json([
            'messages' => $messages->map(fn (Message $message) => $this->payload($message, $request->user()))->all(),

            /*
             * Hasta qué mensaje mío ha leído la otra persona. Va como un solo
             * número y no como una lista porque un chat se lee en orden: con el
             * último basta para encender todos los visos anteriores, y así esta
             * respuesta no crece con la conversación.
             */
            'read_up_to' => (int) $conversation->messages()
                ->where('sender_id', $request->user()->id)
                ->whereNotNull('read_at')
                ->max('id'),
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(Message $message, User $reader): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'mine' => $message->sender_id === $reader->id,
            'at' => ChatTime::hour($message->created_at),
            'day' => ChatTime::day($message->created_at),
        ];
    }
}
