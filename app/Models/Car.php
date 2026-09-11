<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Support\Money;
use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'plate', 'brand', 'model', 'registration_year', 'kilometres', 'fuel',
    'transmission', 'body_type', 'colour', 'seats', 'doors', 'power_hp',
    'has_insurance', 'price_cents', 'description', 'address', 'city',
    'province_code', 'postal_code', 'country', 'latitude', 'longitude',
    'parking_type', 'published',
])]
class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'has_insurance' => 'boolean',
            'published' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<Province, $this> */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /** @return HasMany<CarPhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(CarPhoto::class)->orderBy('position');
    }

    /** @return BelongsToMany<Feature, $this> */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class)->orderBy('position');
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function title(): string
    {
        return "{$this->brand} {$this->model}";
    }

    public function coverPhoto(): ?CarPhoto
    {
        return $this->photos->first();
    }

    public function priceForHumans(): string
    {
        return Money::short($this->price_cents);
    }

    /**
     * «Ronda · Málaga», pero sólo «Málaga» cuando la ciudad es la propia capital:
     * «Málaga · Málaga» no le dice nada a nadie.
     */
    public function place(): string
    {
        $province = $this->province->name;

        return $this->city === $province ? $province : "{$this->city} · {$province}";
    }

    /** Sólo lo que el catálogo puede enseñar. */
    public function scopePublished(Builder $query): void
    {
        $query->where('published', true);
    }

    /**
     * Libre entre dos fechas, ambas incluidas. Dos rangos se pisan cuando cada uno
     * empieza antes de que el otro acabe: es la única comparación que hace falta, y
     * evita las cuatro condiciones con BETWEEN que llevaba el TFG.
     */
    public function scopeFreeBetween(Builder $query, string $from, string $to): void
    {
        $query->whereDoesntHave('bookings', function (Builder $bookings) use ($from, $to) {
            $bookings
                ->whereIn('status', array_map(fn (BookingStatus $status) => $status->value, BookingStatus::blocking()))
                ->where('starts_on', '<=', $to)
                ->where('ends_on', '>=', $from);
        });
    }
}
