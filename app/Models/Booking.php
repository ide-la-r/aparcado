<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\BookingStatus;
use App\Support\DateRange;
use App\Support\Money;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

#[Fillable(['starts_on', 'ends_on', 'days', 'price_cents_per_day', 'total_cents', 'status'])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_on' => DateOnly::class,
            'ends_on' => DateOnly::class,
            'status' => BookingStatus::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Car, $this> */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /** @return BelongsTo<User, $this> */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    /** @return MorphMany<Payment, $this> */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function blocksDates(): bool
    {
        return in_array($this->status, BookingStatus::blocking(), true);
    }

    public function datesForHumans(): string
    {
        return DateRange::forHumans($this->starts_on->toDateString(), $this->ends_on->toDateString());
    }

    public function totalForHumans(): string
    {
        return Money::format($this->total_cents);
    }

    public function breakdownForHumans(): string
    {
        return trans_choice(':count día|:count días', $this->days, ['count' => $this->days])
            .' × '.Money::short($this->price_cents_per_day);
    }

    /**
     * Se puede cancelar mientras no haya empezado. Una vez el coche está entregado,
     * lo que pase se arregla entre las dos personas y no con un botón.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, [BookingStatus::Pending, BookingStatus::Confirmed], true)
            && $this->starts_on->greaterThan(Carbon::today());
    }

    public function hasFinished(): bool
    {
        return $this->ends_on->lessThan(Carbon::today());
    }
}
