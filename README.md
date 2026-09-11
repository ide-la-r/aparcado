# Aparcado

Alquiler de coches entre particulares. El dueño publica su coche cuando no lo usa,
otra persona lo reserva por días, y los dos hablan por el chat de la propia web
antes y durante el alquiler.

## De dónde viene

Es la reconstrucción de **SocialiCar**, mi Trabajo de Fin de Grado, hecho en equipo
con [@pma152402](https://github.com/pma152402). La idea, el alcance y las decisiones
de producto son de aquel trabajo conjunto; el código de aquí es nuevo y mío.

Aquello era PHP a mano: consultas sueltas en las vistas, ficheros de 79 KB, diecisiete
hojas de estilo y un pago de PayPal que se resolvía entero en el navegador y no dejaba
rastro en la base de datos. Funcionaba, y aprendí montándolo. Esto es lo mismo bien
hecho: Laravel, una capa de datos con relaciones de verdad, los pagos verificados en el
servidor y tests.

## Qué hace

- **Catálogo** de coches por provincia y fechas, que sólo enseña los que están libres
  en el rango pedido.
- **Ficha del coche** con fotos, extras, situación en el mapa y precio por día.
- **Reservas** por rango de fechas, sin solaparse con las que ya existen.
- **Perfil** con verificación de identidad (DNI/NIE/pasaporte y carné de conducir).
- **Planes de suscripción** para dueños: el plan decide qué puesto ocupa tu coche en
  el catálogo.
- **Chat** entre dueño e interesado, por conversación.
- **Pagos** con PayPal: la orden se crea y se captura en el servidor, se comprueba el
  importe y queda registrada.

## Cómo se levanta

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
composer run dev
```

## Los pagos

La orden se crea y se captura **en el servidor**, con el importe que dice la base
de datos, y queda registrada en la tabla `payments`. El navegador sólo recibe el
identificador de la orden.

Para que funcionen hay que poner en el entorno las credenciales de una aplicación
de PayPal —developer.paypal.com, «Apps & Credentials», con el conmutador en
«Sandbox»—:

```
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
PAYPAL_WEBHOOK_ID=...
```

Sin ellas la pantalla de pago lo dice y no se rompe nada.

## Cómo se despliega

Render (plan gratuito, sin tarjeta) + Postgres de Neon + GitHub Actions para lo
que en un servidor normal haría el cron. El contenedor es FrankenPHP: servidor
web y PHP en un solo proceso, que en 512 MB de RAM se nota.

1. **Base de datos.** Un proyecto en [Neon](https://neon.tech) y su cadena de
   conexión en `DB_URL`. El Postgres gratuito de Render no vale: caduca a los 30
   días.
2. **Servicio.** «New > Blueprint» apuntando a este repositorio; `render.yaml`
   trae todo lo demás. Las variables marcadas `sync: false` se rellenan a mano
   (`APP_KEY` sale de `php artisan key:generate --show`).
3. **Secretos del repositorio en GitHub**, para los flujos programados:
   `APP_URL` y `INTERNAL_TASK_TOKEN` (el mismo valor que la variable del
   servicio).
4. **Subidas.** El disco del contenedor **se borra en cada despliegue**. Con
   `UPLOADS_PUBLIC_DISK=public` las fotos duran hasta el siguiente; para que
   sobrevivan hay que poner un bucket S3 —Cloudflare R2 o Backblaze B2, los dos
   con 10 GB gratis— y cambiar los dos discos a `s3` y `s3-private`.

El servicio duerme tras quince minutos sin tráfico y tarda medio minuto en
levantarse. `keepalive.yml` lo mantiene despierto de 7 a 22, y de noche lo deja
dormir.

## Cómo está montado

| Pieza | Dónde |
| --- | --- |
| Modelos y relaciones | `app/Models` |
| Lógica de dominio | `app/Services` |
| Pantallas | `resources/views` |
| Esquema | `database/migrations` |
| Datos de ejemplo | `database/seeders` |
| Tests | `tests/Feature`, `tests/Unit` |

Las convenciones del repositorio están en [`AGENTS.md`](AGENTS.md).

## Estado

En construcción. Lo que hay hecho y lo que falta, en
[las incidencias](https://github.com/ide-la-r/aparcado/issues).
