import Alpine from 'alpinejs';

window.Alpine = Alpine;

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
