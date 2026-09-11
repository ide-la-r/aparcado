<?php

namespace App\Enums;

enum Plan: string
{
    case Premium = 'premium';
    case Plus = 'plus';

    public function label(): string
    {
        return $this->config('name');
    }

    public function priceCents(): int
    {
        return (int) $this->config('price_cents');
    }

    /**
     * Lo que se compra: cuanto más alto, antes sale el coche en el catálogo. Quien
     * no tiene plan vale 0, así que el orden sale solo.
     */
    public function priority(): int
    {
        return (int) $this->config('priority');
    }

    /**
     * El trozo de SQL que traduce el plan guardado en una fila a su prioridad, con
     * sus valores como parámetros. Está aquí para que el orden del catálogo y los
     * precios salgan del mismo sitio: un plan nuevo en la configuración entra solo.
     *
     * @return array{0: string, 1: array<int, string|int>}
     */
    public static function sqlPriorityCase(string $column): array
    {
        $sql = 'case '.$column;
        $bindings = [];

        foreach (self::cases() as $plan) {
            $sql .= ' when ? then ?';
            $bindings[] = $plan->value;
            $bindings[] = $plan->priority();
        }

        return [$sql.' else 0 end', $bindings];
    }

    private function config(string $key): mixed
    {
        return config("aparcado.plans.{$this->value}.{$key}");
    }
}
