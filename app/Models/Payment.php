<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'captured_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Lo que se está pagando: hoy una reserva, mañana también una suscripción. */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function amountForHumans(): string
    {
        return Money::format($this->amount_cents);
    }

    public function isCaptured(): bool
    {
        return $this->status === PaymentStatus::Captured;
    }
}
