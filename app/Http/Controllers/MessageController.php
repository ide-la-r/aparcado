<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Chat\Conversations;
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

    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('send', $conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [], ['body' => 'el mensaje']);

        $this->conversations->send($conversation, $request->user(), $validated['body']);

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
            'messages' => $messages->map(fn (Message $message) => [
                'id' => $message->id,
                'body' => $message->body,
                'mine' => $message->sender_id === $request->user()->id,
                'at' => $message->created_at->format('H:i'),
            ])->all(),
        ]);
    }
}
