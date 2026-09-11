<?php

namespace App\Services\Catalogue;

use App\Enums\Plan;
use App\Models\Car;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * La búsqueda del catálogo. El TFG resolvía el orden con tres consultas seguidas
 * —los Premium con LIMIT 3, los Plus con LIMIT 6, y el resto— pegadas una detrás
 * de otra en la vista, con el mismo bloque de fechas copiado tres veces. Aquí es
 * una consulta con la prioridad del plan calculada dentro.
 */
class CarSearch
{
    public function __construct(
        private readonly ?string $provinceCode = null,
        private readonly ?string $from = null,
        private readonly ?string $to = null,
    ) {}

    public static function fromFilters(array $filters): self
    {
        return new self(
            provinceCode: $filters['province'] ?? null,
            from: $filters['from'] ?? null,
            to: $filters['to'] ?? null,
        );
    }

    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage)->withQueryString();
    }

    public function query(): Builder
    {
        [$case, $bindings] = Plan::sqlPriorityCase('subscriptions.plan');
        $today = Carbon::today()->toDateString();

        $query = Car::query()
            ->published()
            ->with(['province', 'photos'])
            /*
             * La prioridad se saca con una subconsulta y no con un join: un dueño
             * podría tener dos suscripciones solapadas y el join duplicaría sus
             * coches en la rejilla. Con `max` se queda la mejor que tenga.
             */
            ->select('cars.*')
            ->selectRaw(
                "(select coalesce(max({$case}), 0) from subscriptions"
                .' where subscriptions.user_id = cars.owner_id'
                .' and subscriptions.starts_on <= ? and subscriptions.ends_on >= ?) as plan_priority',
                [...$bindings, $today, $today],
            )
            ->orderByDesc('plan_priority')
            ->orderBy('price_cents')
            // El id al final para que dos coches al mismo precio no bailen de
            // página en página.
            ->orderBy('cars.id');

        if ($this->provinceCode !== null) {
            $query->where('province_code', $this->provinceCode);
        }

        if ($this->from !== null && $this->to !== null) {
            $query->freeBetween($this->from, $this->to);
        }

        return $query;
    }
}
