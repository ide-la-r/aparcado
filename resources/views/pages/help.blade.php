<x-layouts.app title="Ayuda">
    <x-page-header title="Ayuda"
                   lead="Lo que más se pregunta, en una sola página." />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <div class="space-y-3">
            @foreach ([
                ['¿Hace falta verificar la cuenta para todo?', 'No. Con la cuenta recién hecha ya puedes buscar coches, ver las fichas y escribir a los dueños. Lo que sí pide que comprobemos tu documento de identidad y tu carné es alquilar un coche y publicar el tuyo: es lo que hace que un desconocido te deje el suyo.'],
                ['¿Cuándo quedan mías las fechas?', 'Al pagar, no al pedirlo. Una reserva sin pagar no bloquea el coche, así que dos personas pueden tenerlo pendiente a la vez y se lo queda quien pague primero. Si alguien se te adelanta después de haber pagado tú, se te devuelve el dinero.'],
                ['¿Puedo cancelar?', 'Sí, mientras el alquiler no haya empezado, y lo puede hacer cualquiera de los dos. Una vez el coche está entregado, eso se arregla hablando.'],
                ['Si subo el precio de mi coche, ¿cambia lo que ya me reservaron?', 'No. El precio se congela el día en que alguien reserva. Lo nuevo se aplica a las reservas que lleguen después.'],
                ['Necesito el coche una semana, ¿lo puedo esconder?', 'Sí. En «Mis coches» puedes quitarlo del catálogo sin borrarlo, y las reservas que ya tuviera siguen en pie. Retirarlo del todo es otra cosa, y eso no se deshace.'],
                ['¿Se ve mi dirección?', 'No. En la ficha se ve la ciudad, la provincia y una zona aproximada en el mapa. Dónde se recoge exactamente lo dices tú por el chat.'],
                ['¿Dónde acaban las fotos de mi DNI?', 'En un disco privado, sin ninguna dirección pública que lleve a ellas, y sólo las ve quien comprueba la cuenta. Está contado en la página de privacidad.'],
                ['¿Para qué sirven los planes?', 'Para el sitio que ocupa tu coche en el catálogo de tu provincia: los de un Premium salen antes que los de un Plus, y esos antes que los de quien no paga nada. Publicar es gratis siempre.'],
                ['¿Se mueve dinero de verdad?', 'No. Los pagos apuntan al entorno de pruebas de PayPal, porque esto es un proyecto personal y no un servicio real.'],
            ] as $index => [$pregunta, $respuesta])
                <details class="card group p-5" @if ($index === 0) open @endif>
                    <summary class="flex cursor-pointer items-center justify-between gap-4 font-semibold text-neutral-900 marker:content-none">
                        {{ $pregunta }}

                        <svg class="h-5 w-5 shrink-0 text-neutral-400 transition group-open:rotate-180"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </summary>

                    <p class="mt-3 text-neutral-700">{{ $respuesta }}</p>
                </details>
            @endforeach
        </div>

        <p class="mt-8 text-sm text-neutral-600">
            ¿No está aquí? <a href="{{ route('pages.contact') }}"
                              class="font-medium text-brand-700 underline decoration-brand-300 underline-offset-2 hover:decoration-brand-600">Escríbenos</a>.
        </p>
    </div>
</x-layouts.app>
