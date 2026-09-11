<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\Plan;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['plan', 'price_cents', 'starts_on', 'ends_on'])]
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'plan' => Plan::class,
            'starts_on' => DateOnly::class,
            'ends_on' => DateOnly::class,
            'cancelled_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cancelar no quita lo pagado: la suscripción sigue valiendo hasta su fecha de
     * fin, simplemente no se renueva.
     */
    public function isActive(?Carbon $on = null): bool
    {
        $day = ($on ?? Carbon::today())->startOfDay();

        return $this->starts_on->lessThanOrEqualTo($day)
            && $this->ends_on->greaterThanOrEqualTo($day);
    }

    public function scopeActive(Builder $query, ?Carbon $on = null): void
    {
        $day = ($on ?? Carbon::today())->toDateString();

        $query->where('starts_on', '<=', $day)->where('ends_on', '>=', $day);
    }
}
