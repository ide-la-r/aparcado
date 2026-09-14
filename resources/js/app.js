import Alpine from 'alpinejs';

window.Alpine = Alpine;

/*
 * Los visos del chat: uno cuando el mensaje ha salido, dos cuando lo han leído.
 * Se pintan desde aquí tanto los de los mensajes que ya venían en la página como
 * los de los que van llegando, así que no hay dos versiones que se puedan
 * desparejar.
 */
const VISO = {
    enviado: '<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2Z"/></svg>',
    leido: '<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.4 12.6 4.6 16.8 6 15.4 1.8 11.2.4 12.6Zm17.9-7.5-8.9 8.9-3.5-3.5-1.4 1.4 4.9 4.9L19.7 6.5l-1.4-1.4Zm4.3 0L12.3 15.4l1.4 1.4L24 6.5l-1.4-1.4Z"/></svg>',
};

/*
 * El chat.
 *
 * Está aquí y no dentro del `x-data` de la vista porque son cien líneas de
 * lógica: metidas en un atributo HTML no hay quien las lea ni quien las arregle.
 */
Alpine.data('chat', (opciones) => ({
    last: opciones.last,
    readUpTo: opciones.readUpTo,
    // La etiqueta del último día pintado, para saber cuándo hace falta otra raya.
    dia: opciones.dia,

    texto: '',
    enviando: false,
    mirando: false,
    error: '',

    arrancar() {
        this.visos();
        this.abajo();
        setInterval(() => this.mirar(), 5000);
    },

    /** Repasa los visos de mis mensajes: uno si ha salido, dos si lo han leído. */
    visos() {
        for (const hueco of this.$refs.hilo.querySelectorAll('[data-viso]')) {
            const leido = Number(hueco.dataset.viso) <= this.readUpTo;
            const quiero = leido ? VISO.leido : VISO.enviado;

            // Sólo se toca lo que cambia: reescribir todos los visos en cada
            // sondeo hace parpadear el hilo entero cada cinco segundos.
            if (hueco.dataset.estado !== (leido ? 'leido' : 'enviado')) {
                hueco.innerHTML = quiero;
                hueco.dataset.estado = leido ? 'leido' : 'enviado';
                hueco.title = leido ? 'Leído' : 'Enviado';
            }
        }
    },

    /** Lo que va llegando de la otra persona. */
    async mirar() {
        if (this.mirando) return;
        this.mirando = true;

        try {
            const respuesta = await fetch(`${opciones.poll}?after=${this.last}`, {
                headers: { Accept: 'application/json' },
            });

            if (! respuesta.ok) return;

            const { messages, read_up_to: leidoHasta } = await respuesta.json();

            // Los visos se repasan aunque no haya mensajes nuevos: que te lean no
            // llega como un mensaje.
            this.readUpTo = Math.max(this.readUpTo, leidoHasta ?? 0);
            this.visos();

            const pegado = this.pegadoAbajo();

            for (const mensaje of messages) {
                this.pintar(mensaje);
                this.last = mensaje.id;
            }

            // Sólo se baja solo si ya estabas abajo: si estás leyendo lo de arriba,
            // que el hilo te dé un tirón cada cinco segundos es insufrible.
            if (messages.length && pegado) this.abajo();
        } catch {
            /* Un sondeo que falla no se avisa: vuelve a intentarlo en cinco
               segundos y no hay nada que el usuario pueda hacer. */
        } finally {
            this.mirando = false;
        }
    },

    async enviar(form) {
        const texto = this.texto.trim();

        if (! texto || this.enviando) return;

        this.enviando = true;
        this.error = '';
        this.texto = '';
        this.crecer();

        try {
            const respuesta = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ body: texto }),
            });

            if (! respuesta.ok) throw new Error(respuesta.status);

            const { message } = await respuesta.json();

            this.pintar(message);
            this.last = message.id;
            this.abajo();
        } catch {
            /* Se devuelve lo escrito al cuadro: perder un mensaje ya redactado
               porque se fue la conexión un segundo es de lo que peor sienta. */
            this.texto = texto;
            this.crecer();
            this.error = 'No se ha podido enviar. Inténtalo otra vez.';
        } finally {
            this.enviando = false;
            this.$refs.caja.focus();
        }
    },

    pintar(mensaje) {
        this.$refs.vacio?.remove();

        if (mensaje.day !== this.dia) {
            const raya = document.createElement('p');
            raya.className = 'chat-day';
            const etiqueta = document.createElement('span');
            etiqueta.textContent = mensaje.day;
            raya.appendChild(etiqueta);
            this.$refs.hilo.appendChild(raya);
            this.dia = mensaje.day;
        }

        const fila = document.createElement('div');
        fila.className = `flex pt-2 ${mensaje.mine ? 'justify-end' : 'justify-start'}`;

        const burbuja = document.createElement('div');
        burbuja.className = `bubble ${mensaje.mine ? 'bubble-mine' : 'bubble-theirs'}`;

        /* `textContent` y no `innerHTML`: un mensaje es texto de otra persona, y
           por ahí es por donde entra un script ajeno. */
        const cuerpo = document.createElement('p');
        cuerpo.className = 'whitespace-pre-line';
        cuerpo.textContent = mensaje.body;

        const hora = document.createElement('p');
        hora.className = `bubble-time ${mensaje.mine ? 'text-brand-100/80' : 'text-neutral-400'}`;
        hora.textContent = mensaje.at;

        if (mensaje.mine) {
            const viso = document.createElement('span');
            viso.dataset.viso = mensaje.id;
            hora.appendChild(viso);
        }

        burbuja.append(cuerpo, hora);
        fila.appendChild(burbuja);
        this.$refs.hilo.appendChild(fila);

        this.visos();
    },

    /** El cuadro de texto crece con lo escrito, hasta el tope que pone el CSS. */
    crecer() {
        const caja = this.$refs.caja;
        caja.style.height = 'auto';
        caja.style.height = `${caja.scrollHeight}px`;
    },

    pegadoAbajo() {
        const hilo = this.$refs.hilo;

        return hilo.scrollHeight - hilo.scrollTop - hilo.clientHeight < 80;
    },

    abajo() {
        this.$refs.hilo.scrollTop = this.$refs.hilo.scrollHeight;
    },
}));

Alpine.start();

/*
 * Lo que lleva `data-reveal` entra desde abajo al aparecer en pantalla.
 *
 * El estado inicial lo pone este archivo y no el CSS: si estuviera en la hoja de
 * estilos y alguien tuviera el JavaScript apagado, la página se quedaría en
 * blanco para siempre. Así, sin JavaScript, simplemente no hay animación.
 */
const reveal = () => {
    const piezas = document.querySelectorAll('[data-reveal]');

    if (piezas.length === 0) return;

    // Si el sistema pide menos movimiento, no hay nada que animar.
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    piezas.forEach((pieza) => pieza.classList.add('revealable'));

    let avisoRecibido = false;

    const observador = new IntersectionObserver(
        (entradas) => {
            entradas.forEach((entrada) => {
                if (! entrada.isIntersecting) return;

                avisoRecibido = true;

                // El retraso escalona los hermanos para que entren en cascada y no
                // todos de golpe.
                const retraso = Number(entrada.target.dataset.reveal) || 0;

                setTimeout(() => entrada.target.classList.add('revealed'), retraso);
                observador.unobserve(entrada.target);
            });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.1 },
    );

    piezas.forEach((pieza) => observador.observe(pieza));

    /*
     * Y una red debajo. Lo que espera a aparecer está a opacidad cero, así que si
     * el observador no avisa nunca —un navegador que deja de pintar la pestaña, un
     * entorno que limita el dibujado— media página se queda invisible para siempre
     * y sin un solo error en ninguna parte. Pasó aquí mismo: de doce tarjetas se
     * veía una.
     *
     * Salta sólo si NO ha avisado ni una vez. En cuanto el observador funciona esto
     * no hace nada y la aparición escalonada se queda igual.
     */
    setTimeout(() => {
        if (avisoRecibido) return;

        observador.disconnect();
        piezas.forEach((pieza) => pieza.classList.add('revealed'));
    }, 2000);
};

document.addEventListener('DOMContentLoaded', reveal);
