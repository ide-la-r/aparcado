<?php

namespace App\Policies;

use App\Models\Car;
use App\Models\User;

class CarPolicy
{
    /** El coche es de quien lo publicó, y de nadie más. */
    public function update(User $user, Car $car): bool
    {
        return $user->id === $car->owner_id;
    }

    public function delete(User $user, Car $car): bool
    {
        return $this->update($user, $car);
    }
}
