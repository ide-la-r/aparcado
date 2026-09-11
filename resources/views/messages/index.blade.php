<x-layouts.app title="Mensajes">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Mensajes</h1>

        @if ($conversations->isEmpty())
            <div class="card mt-8 p-10 text-center">
                <p class="font-semibold">Aquí no hay nada todavía.</p>
                <p class="mt-1.5 text-sm text-neutral-600">
                    Las conversaciones empiezan en la ficha de un coche, preguntando al dueño.
                </p>
                <a href="{{ route('cars.index') }}" class="btn btn-primary mt-5">Ver coches</a>
            </div>
        @else
            <div class="mt-8 space-y-2">
                @foreach ($conversations as $conversation)
                    @php
                        $counterpart = $conversation->counterpartFor(auth()->user());
                        $last = $conversation->lastMessage;
                    @endphp

                    <a href="{{ route('messages.show', $conversation) }}"
                       class="card flex items-center gap-4 p-4 transition hover:ring-brand-300">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-neutral-200 font-semibold text-neutral-600">
                            {{ mb_substr($counterpart?->name ?? '?', 0, 1) }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate font-semibold">{{ $counterpart?->name ?? 'Alguien' }}</p>

                                @if ($conversation->unread_count > 0)
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-600 px-1.5 text-xs font-semibold text-white">
                                        {{ $conversation->unread_count }}
                                    </span>
                                @endif
                            </div>

                            <p class="truncate text-sm text-neutral-500">
                                {{ $conversation->car?->title() ?? 'Un coche que ya no está' }}
                            </p>

                            @if ($last)
                                <p class="mt-1 truncate text-sm text-neutral-600">{{ $last->body }}</p>
                            @endif
                        </div>

                        @if ($conversation->last_message_at)
                            <span class="shrink-0 text-xs text-neutral-400">
                                {{ $conversation->last_message_at->diffForHumans(short: true) }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
