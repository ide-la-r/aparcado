#!/bin/sh
set -e

# Render —y cualquier plataforma parecida— inyecta el puerto por variable de
# entorno.
export SERVER_NAME=":${PORT:-80}"

echo "→ Preparando la aplicación"

# Las cachés se regeneran en cada arranque: las variables de entorno cambian
# entre despliegues y una caché vieja las ignoraría.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# El enlace de storage sólo hace falta cuando las subidas van al disco local.
# Si ya existe, o si las fotos van a S3, esto no estorba.
php artisan storage:link 2>/dev/null || true

# Sin --isolated a propósito: ese modo necesita el almacén de caché, que aquí es
# la propia base de datos y en el primer despliegue todavía no tiene tablas. Con
# un único contenedor no hay carrera que evitar; si algún día hay varias
# instancias, las migraciones tienen que salir a un paso previo.
php artisan migrate --force || {
    echo "✗ Las migraciones han fallado"
    exit 1
}

# Los datos de referencia —las 52 provincias y el catálogo de extras— NO son datos
# de ejemplo: sin ellos no se puede publicar un coche, porque la provincia es una
# clave ajena, y el buscador sale con el desplegable vacío. Se siembran en cada
# arranque porque los dos sembradores hacen `upsert`: repetirlo no duplica nada, y
# así una provincia o un extra nuevos entran solos en el siguiente despliegue.
php artisan db:seed --class='Database\Seeders\ReferenceDataSeeder' --force || {
    echo "✗ No se han podido sembrar los datos de referencia"
    exit 1
}

# Las fotos de los coches de ejemplo van dentro del repositorio, así que lo que
# manda es `config/demo_photos.php` y la base de datos se pone al día sola en cada
# arranque: es una reconciliación, no un apaño de una vez. Hizo falta porque los
# ejemplos se sembraron en producción cuando las fotos aún no existían.
#
# Aquí NO se corta el arranque si falla: sin provincias la web no funciona, pero
# con una foto de menos sí.
php artisan aparcado:refresh-demo-photos || echo "⚠ No se han podido reponer las fotos de ejemplo"

echo "→ Servidor escuchando en ${SERVER_NAME}"

exec frankenphp run --config /etc/caddy/Caddyfile
