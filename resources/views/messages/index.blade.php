<x-layouts.app title="Mensajes">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Mensajes</h1>

        @if ($conversations->isEmpty())
            <div class="card mt-8 p-12 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9l-5 4V6a2 2 0 0 1 2-2Z" />
                    </svg>
                </span>

                <p class="mt-4 font-display text-lg font-semibold">Aquí no hay nada todavía</p>
                <p class="mx-auto mt-1.5 max-w-xs text-sm text-neutral-600">
                    Las conversaciones empiezan en la ficha de un coche, preguntando al dueño.
                </p>
                <a href="{{ route('cars.index') }}" class="btn btn-primary mt-6">Ver coches</a>
            </div>
        @else
            <div class="mt-8 space-y-2">
                @foreach ($conversations as $conversation)
                    @php
                        $counterpart = $conversation->counterpartFor(auth()->user());
                        $last = $conversation->lastMessage;
                        $unread = $conversation->unread_count > 0;
                    @endphp

                    <a href="{{ route('messages.show', $conversation) }}"
                       @class([
                           'card card-hover group flex items-center gap-4 p-3 sm:p-4',
                           'bg-brand-50/40 ring-brand-200' => $unread,
                       ])>
                        {{-- La foto del coche delante: en una bandeja con varias
                             conversaciones, es lo que las distingue de un vistazo. --}}
                        @if ($conversation->car)
                            <x-car-photo :car="$conversation->car"
                                         class="aspect-[4/3] w-20 shrink-0 rounded-xl ring-1 ring-neutral-900/5 sm:w-24" />
                        @else
                            <span class="flex h-15 w-20 shrink-0 items-center justify-center rounded-xl bg-neutral-100 text-neutral-400 sm:w-24">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9l-5 4V6a2 2 0 0 1 2-2Z" />
                                </svg>
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate font-semibold transition group-hover:text-brand-700">
                                    {{ $counterpart?->name ?? 'Alguien' }}
                                </p>

                                @if ($unread)
                                    <span class="flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-brand-600 px-1.5 text-xs font-semibold text-white">
                                        {{ $conversation->unread_count }}
                                    </span>
                                @endif
                            </div>

                            <p class="truncate text-sm text-neutral-500">
                                {{ $conversation->car?->title() ?? 'Un coche que ya no está' }}
                            </p>

                            @if ($last)
                                {{-- «Tú:» delante de lo último cuando lo escribiste tú:
                                     sin eso, la bandeja parece llena de mensajes sin
                                     contestar que en realidad son tuyos. --}}
                                <p @class([
                                    'mt-1 truncate text-sm',
                                    'font-medium text-neutral-900' => $unread,
                                    'text-neutral-600' => ! $unread,
                                ])>
                                    @if ($last->sender_id === auth()->id())
                                        <span class="text-neutral-400">Tú:</span>
                                    @endif
                                    {{ $last->body }}
                                </p>
                            @endif
                        </div>

                        @if ($conversation->last_message_at)
                            <span class="shrink-0 self-start text-xs text-neutral-400">
                                {{ $conversation->last_message_at->diffForHumans(short: true) }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
