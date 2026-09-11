<x-layouts.app :title="$counterpart?->name ?? 'Conversación'">
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6">
        <a href="{{ route('messages.index') }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-900">
            ← Todos los mensajes
        </a>

        <header class="card mt-4 flex items-center gap-4 p-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-neutral-200 font-semibold text-neutral-600">
                {{ mb_substr($counterpart?->name ?? '?', 0, 1) }}
            </span>

            <div class="min-w-0 flex-1">
                <h1 class="truncate font-semibold">{{ $counterpart?->name ?? 'Alguien' }}</h1>

                @if ($conversation->car)
                    <p class="truncate text-sm text-neutral-500">
                        Sobre <a href="{{ route('cars.show', $conversation->car) }}"
                                 class="font-medium text-brand-700 hover:underline">{{ $conversation->car->title() }}</a>
                    </p>
                @else
                    <p class="text-sm text-neutral-500">El coche del que hablabais ya no está publicado.</p>
                @endif
            </div>
        </header>

        <div x-data="{
                last: {{ $messages->last()?->id ?? 0 }},
                cargando: false,

                async mirar() {
                    if (this.cargando) return;
                    this.cargando = true;

                    try {
                        const respuesta = await fetch(@js(route('messages.poll', $conversation)) + '?after=' + this.last, {
                            headers: { 'Accept': 'application/json' },
                        });

                        if (! respuesta.ok) return;

                        const { messages } = await respuesta.json();

                        for (const mensaje of messages) {
                            this.pintar(mensaje);
                            this.last = mensaje.id;
                        }

                        if (messages.length) this.abajo();
                    } finally {
                        /* Se apaga aquí y no antes: si una respuesta se queda colgada,
                           el sondeo tiene que poder volver a intentarlo. */
                        this.cargando = false;
                    }
                },

                pintar(mensaje) {
                    const hueco = document.createElement('div');
                    hueco.className = 'flex ' + (mensaje.mine ? 'justify-end' : 'justify-start');

                    const burbuja = document.createElement('div');
                    burbuja.className = mensaje.mine
                        ? 'max-w-[80%] rounded-2xl bg-brand-600 px-3.5 py-2 text-sm text-white'
                        : 'max-w-[80%] rounded-2xl bg-white px-3.5 py-2 text-sm text-neutral-900 ring-1 ring-neutral-200';

                    /* `textContent` y no `innerHTML`: un mensaje es texto de otra
                       persona, y por ahí es por donde entra un script ajeno. */
                    burbuja.textContent = mensaje.body;

                    const hora = document.createElement('span');
                    hora.className = 'mt-1 block text-right text-[11px] ' + (mensaje.mine ? 'text-brand-100' : 'text-neutral-400');
                    hora.textContent = mensaje.at;

                    burbuja.appendChild(hora);
                    hueco.appendChild(burbuja);
                    this.$refs.hilo.appendChild(hueco);
                },

                abajo() {
                    this.$refs.hilo.scrollTop = this.$refs.hilo.scrollHeight;
                },
             }"
             x-init="abajo(); setInterval(() => mirar(), 5000)"
             class="mt-4">

            <div x-ref="hilo" class="max-h-[26rem] space-y-2 overflow-y-auto rounded-2xl bg-neutral-100 p-4">
                @forelse ($messages as $message)
                    @php $mine = $message->sender_id === auth()->id(); @endphp

                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                        <div class="{{ $mine
                            ? 'max-w-[80%] rounded-2xl bg-brand-600 px-3.5 py-2 text-sm text-white'
                            : 'max-w-[80%] rounded-2xl bg-white px-3.5 py-2 text-sm text-neutral-900 ring-1 ring-neutral-200' }}">
                            {{ $message->body }}
                            <span class="mt-1 block text-right text-[11px] {{ $mine ? 'text-brand-100' : 'text-neutral-400' }}">
                                {{ $message->created_at->format('H:i') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-neutral-500">
                        Pregunta lo que quieras: si cabe la silla del niño, dónde se recoge, la hora.
                    </p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('messages.store', $conversation) }}" class="mt-3 flex gap-2">
                @csrf

                <input name="body" class="field" maxlength="2000" required autofocus
                       placeholder="Escribe un mensaje" autocomplete="off">

                <button type="submit" class="btn btn-primary shrink-0">Enviar</button>
            </form>

            @error('body')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</x-layouts.app>
