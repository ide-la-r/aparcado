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

    const observador = new IntersectionObserver(
        (entradas) => {
            entradas.forEach((entrada) => {
                if (! entrada.isIntersecting) return;

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
};

document.addEventListener('DOMContentLoaded', reveal);
