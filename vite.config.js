import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            /*
             * Una sola familia para todo: Plus Jakarta Sans. Antes eran dos —Space
             * Grotesk arriba e Inter abajo— y la mezcla no acababa de cuadrar: la
             * de los titulares tiraba a técnica y la página se leía a trozos.
             *
             * Con una sola, lo que separa un titular de un párrafo es el peso y el
             * interletrado, no un cambio de letra, y el conjunto se ve de una pieza.
             * De paso es una descarga menos.
             *
             * Se bajan al compilar y se sirven desde aquí: ni una petición a un
             * tercero y ni un salto de tipografía al cargar.
             */
            fonts: [
                bunny('Plus Jakarta Sans', { weights: [400, 500, 600, 700, 800] }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
