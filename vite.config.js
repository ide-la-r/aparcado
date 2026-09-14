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
             * Dos tipografías, y la pareja es medio diseño: Space Grotesk para los
             * titulares —mecánica, con carácter, va con un sitio de coches— e Inter
             * para leer, que es lo que mejor se lee a tamaño pequeño. Con una sola
             * fuente para todo la página sale correcta y sin personalidad.
             *
             * Se descargan en la compilación y se sirven desde aquí: ni una
             * petición a un tercero y ni un salto de tipografía al cargar.
             */
            fonts: [
                bunny('Space Grotesk', { weights: [500, 600, 700] }),
                bunny('Inter', { weights: [400, 500, 600, 700] }),
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
