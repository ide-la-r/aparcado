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

echo "→ Servidor escuchando en ${SERVER_NAME}"

exec frankenphp run --config /etc/caddy/Caddyfile
