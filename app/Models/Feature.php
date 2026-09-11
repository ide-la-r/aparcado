<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    public $timestamps = false;

    /** @return BelongsToMany<Car, $this> */
    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class);
    }
}
