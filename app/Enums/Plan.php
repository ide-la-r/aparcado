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

    /** Cuántos huecos de cabecera se reparten entre los coches de este plan. */
    public function slots(): int
    {
        return (int) $this->config('slots');
    }

    private function config(string $key): mixed
    {
        return config("aparcado.plans.{$this->value}.{$key}");
    }
}
