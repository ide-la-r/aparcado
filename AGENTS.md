# Aparcado — convenciones del repositorio

Proyecto personal. Laravel 13 sobre PHP 8.4, Blade + Alpine + Tailwind 4, SQLite en local
y Postgres en producción (Render). No hay Filament, ni Livewire, ni Inertia.

## Estructura

- `app/Models` — modelos y relaciones. Nada de consultas crudas en las vistas.
- `app/Services` — la lógica que no es de un modelo concreto (disponibilidad, orden del
  catálogo, cobros). Un servicio por asunto, con su test unitario.
- `app/Http/Controllers` — controladores finos: validan con un FormRequest, llaman a un
  servicio y devuelven una vista.
- `resources/views/components` — todo fragmento que se repita dos veces se extrae aquí.
- `database/migrations` — una migración nueva para cada cambio de esquema; **nunca** se
  edita una ya aplicada. El nombre lleva la fecha del día.

## Estilo

- Código en **inglés**: clases, métodos, variables, columnas, rutas de fichero.
- Texto de cara al usuario y comentarios, en **español** y con sus tildes.
- PSR-12 y las convenciones de Laravel. `./vendor/bin/pint` antes de cerrar un cambio.
- Clases en StudlyCase, métodos en camelCase, columnas en snake_case, vistas y assets
  en kebab-case.
- Los comentarios explican el **por qué** de una decisión de dominio, no lo que el
  código ya dice.

## Dinero

- Todo importe se guarda en **céntimos**, en un entero. Ni floats ni decimales.
- El importe que se cobra se recalcula **en el servidor** a partir de la reserva. Nunca
  se confía en un precio que llegue del navegador.

## Tests

- `php artisan test`. Feature para pantallas y flujos, Unit para servicios y objetos de
  valor.
- Nombre de la clase por el sujeto (`BookingAvailabilityTest`), del método por lo que
  se espera (`test_rejects_overlapping_dates`).
- Todo cambio de comportamiento entra con un test que falle sin él.

## Git

- `main` es la rama que se despliega. **No se commitea directamente en `main`.**
- Cada cambio va en su rama (`feature/...`, `fix/...`), se sube, se abre una PR y se
  fusiona. Las PR se describen: qué problema, qué solución, cómo se ha comprobado.
- Mensajes de commit en imperativo y en español, con el ámbito delante:
  `feat(catalogo): ...`, `fix(reservas): ...`.

## Atribución de commits

Los commits van **a nombre del usuario**. La única excepción, temporal y acordada, es el
trailer `Co-authored-by:` mientras se consigue la insignia *Pair Extraordinaire* de
GitHub; en cuanto esté al máximo se retira y no vuelve.
