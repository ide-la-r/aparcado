<?php

namespace App\Models;

use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    /** @return BelongsTo<Car, $this> */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<User, $this> */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** @return HasOne<Message, $this> */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /** Las conversaciones de alguien, sea el dueño o el interesado. */
    public function scopeOf(Builder $query, User $user): void
    {
        $query->where(function (Builder $mine) use ($user) {
            $mine->where('owner_id', $user->id)->orWhere('renter_id', $user->id);
        });
    }

    public function includes(User $user): bool
    {
        return in_array($user->id, [$this->owner_id, $this->renter_id], true);
    }

    /** Con quién habla esta persona en esta conversación. */
    public function counterpartFor(User $user): ?User
    {
        return $user->id === $this->owner_id ? $this->renter : $this->owner;
    }
}
