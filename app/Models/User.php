<?php

namespace App\Models;

use App\Casts\DateOnly;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'surname', 'email', 'password', 'phone', 'birthdate',
    'document_type', 'document_number', 'avatar_path',
    'document_photo_path', 'licence_photo_path',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'birthdate' => DateOnly::class,
            'active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /** @return HasMany<Car, $this> */
    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'owner_id');
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'renter_id');
    }

    /** @return HasMany<Subscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function fullName(): string
    {
        return trim("{$this->name} {$this->surname}");
    }

    /**
     * Verificado = alguien ha mirado el DNI y el carné. Hace falta para publicar un
     * coche y para reservar uno; entrar y mirar el catálogo, no.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function hasSentDocuments(): bool
    {
        return $this->document_photo_path !== null && $this->licence_photo_path !== null;
    }

    /** En qué punto está la verificación: sin papeles, esperando, o hecha. */
    public function verificationState(): string
    {
        return match (true) {
            $this->isVerified() => 'verified',
            $this->hasSentDocuments() => 'pending',
            default => 'missing',
        };
    }

    /**
     * Los mensajes que le han escrito y todavía no ha abierto. Lo pinta la cabecera
     * en todas las páginas, así que es una cuenta y no una lista.
     */
    public function unreadMessages(): int
    {
        return Message::query()
            ->whereHas('conversation', fn (Builder $conversation) => $conversation->of($this))
            ->where('sender_id', '!=', $this->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * El plan que tiene hoy, o null si no paga ninguno. Lee de la relación ya
     * cargada cuando la hay, para que un listado no haga una consulta por dueño.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions
            ->first(fn (Subscription $subscription) => $subscription->isActive());
    }
}
