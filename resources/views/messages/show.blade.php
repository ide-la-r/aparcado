@php
    use App\Support\ChatTime;

    $car = $conversation->car;
    $me = auth()->id();

    // Hasta qué mensaje mío ha leído la otra persona. Con el último basta para
    // encender el doble viso de todos los anteriores: un chat se lee en orden.
    $readUpTo = (int) $messages->where('sender_id', $me)->whereNotNull('read_at')->max('id');
@endphp

<x-layouts.app :title="$counterpart?->name ?? 'Conversación'">
    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 sm:py-8">
        <a href="{{ route('messages.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-neutral-500 transition hover:text-neutral-900">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M10.8 18.6 12.2 17.2 8 13h12v-2H8l4.2-4.2-1.4-1.4L4 12l6.8 6.6Z" />
            </svg>
            Todos los mensajes
        </a>

        {{-- La cabecera lleva la foto del coche y no sólo su nombre: es de lo que se
             está hablando, y con la foto delante no hace falta abrir otra pestaña
             para acordarse de cuál era. --}}
        <div class="card mt-4 flex items-center gap-4 overflow-hidden p-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 font-display font-bold text-white">
                {{ mb_strtoupper(mb_substr($counterpart?->name ?? '?', 0, 1)) }}
            </span>

            <div class="min-w-0 flex-1">
                <h1 class="truncate font-display text-lg font-bold">{{ $counterpart?->name ?? 'Alguien' }}</h1>

                @if ($car)
                    <p class="truncate text-sm text-neutral-500">
                        Sobre
                        <a href="{{ route('cars.show', $car) }}" class="font-medium text-brand-700 hover:underline">
                            {{ $car->title() }}
                        </a>
                    </p>
                @else
                    <p class="text-sm text-neutral-500">El coche del que hablabais ya no está publicado.</p>
                @endif
            </div>

            @if ($car)
                <a href="{{ route('cars.show', $car) }}" class="shrink-0">
                    <x-car-photo :car="$car" class="aspect-[4/3] w-24 rounded-xl ring-1 ring-neutral-900/5 sm:w-28" />
                </a>
            @endif
        </div>

        <div x-data="chat({
                 poll: @js(route('messages.poll', $conversation)),
                 last: {{ $messages->last()?->id ?? 0 }},
                 readUpTo: {{ $readUpTo }},
                 dia: @js($messages->last() ? ChatTime::day($messages->last()->created_at) : ''),
             })"
             x-init="arrancar()"
             class="mt-4">

            {{-- El hilo crece con lo que haya, entre un mínimo y un máximo: con
                 altura fija, tres mensajes dejaban media pantalla de gris vacío
                 debajo; sin mínimo, una conversación recién abierta es una raya. --}}
            <div x-ref="hilo"
                 class="max-h-[min(62vh,34rem)] min-h-56 space-y-1 overflow-y-auto rounded-2xl bg-neutral-100 px-4 py-3 ring-1 ring-neutral-900/5 ring-inset">

                @php $previous = null; @endphp

                @forelse ($messages as $message)
                    @php
                        $mine = $message->sender_id === $me;
                        $newDay = $previous === null || ! $previous->created_at->isSameDay($message->created_at);

                        // Mensajes seguidos de la misma persona y del mismo rato van
                        // más juntos, y sólo el último lleva la hora: si no, el hilo
                        // es una columna de relojes.
                        $grouped = ! $newDay
                            && $previous?->sender_id === $message->sender_id
                            && $previous->created_at->diffInMinutes($message->created_at) < 5;

                        $next = $messages->get($loop->index + 1);
                        $lastOfGroup = $next === null
                            || $next->sender_id !== $message->sender_id
                            || ! $next->created_at->isSameDay($message->created_at)
                            || $message->created_at->diffInMinutes($next->created_at) >= 5;
                    @endphp

                    @if ($newDay)
                        <p class="chat-day"><span>{{ ChatTime::day($message->created_at) }}</span></p>
                    @endif

                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }} {{ $grouped ? '' : 'pt-2' }}">
                        <div class="bubble {{ $mine ? 'bubble-mine' : 'bubble-theirs' }}">
                            <p class="whitespace-pre-line">{{ $message->body }}</p>

                            @if ($lastOfGroup)
                                <p class="bubble-time {{ $mine ? 'text-brand-100/80' : 'text-neutral-400' }}">
                                    {{ ChatTime::hour($message->created_at) }}

                                    @if ($mine)
                                        {{-- Un viso si ha salido, dos si lo han leído. Lo
                                             rellena el mismo JavaScript que pinta los
                                             mensajes según llegan, para que un mensaje
                                             recién enviado no se vea distinto del de
                                             encima. --}}
                                        <span data-viso="{{ $message->id }}"></span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>

                    @php $previous = $message; @endphp
                @empty
                    <div x-ref="vacio" class="flex h-full flex-col items-center justify-center px-6 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-brand-600 ring-1 ring-neutral-900/5">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M4 4h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9l-5 4V6a2 2 0 0 1 2-2Z" />
                            </svg>
                        </span>

                        <p class="mt-4 font-semibold text-neutral-700">Aquí no se ha dicho nada todavía</p>
                        <p class="mt-1 max-w-xs text-sm text-neutral-500">
                            Pregunta lo que quieras: si cabe la silla del niño, dónde se recoge, la hora.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- El formulario de siempre sigue funcionando sin JavaScript; con él,
                 Alpine lo intercepta y lo manda por detrás. --}}
            <form method="POST"
                  action="{{ route('messages.store', $conversation) }}"
                  @submit.prevent="enviar($el)"
                  class="mt-3 flex items-end gap-2">
                @csrf

                <textarea x-ref="caja"
                          x-model="texto"
                          @input="crecer()"
                          {{-- Escrito a mano y no con `.enter.exact.prevent`:
                               `.exact` es de Vue, no de Alpine, y Alpine lo toma
                               por el nombre de una tecla —una que no existe—, así
                               que el Intro no hacía absolutamente nada. --}}
                          @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); enviar($el.form) }"
                          name="body"
                          rows="1"
                          maxlength="2000"
                          required
                          autofocus
                          placeholder="Escribe un mensaje"
                          class="field max-h-32 flex-1 resize-none"></textarea>

                <button type="submit"
                        class="btn btn-primary shrink-0"
                        :disabled="enviando || ! texto.trim()">
                    Enviar
                </button>
            </form>

            <p class="mt-1.5 text-xs text-neutral-400">
                Intro envía; Mayús + Intro hace un salto de línea.
            </p>

            <p x-show="error" x-cloak x-text="error" class="mt-1.5 text-sm text-red-600"></p>

            @error('body')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</x-layouts.app>
