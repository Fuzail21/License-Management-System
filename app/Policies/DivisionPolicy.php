<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DivisionPolicy
{
    /**
     * Admin has full unrestricted access.
     * Managers have city-scoped access.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesCity($user, $division->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Validate city_id for creation.
     */
    public function createInCity(User $user, int $cityId): bool
    {
        if ($user->isManager()) {
            return $this->userManagesCity($user, $cityId);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesCity($user, $division->city_id);
        }

        return false;
    }

    /**
     * Validate city_id for update (when changing city).
     */
    public function updateToCity(User $user, Division $division, int $newCityId): bool
    {
        if ($user->isManager()) {
            return $this->userManagesCity($user, $division->city_id)
                && $this->userManagesCity($user, $newCityId);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesCity($user, $division->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesCity($user, $division->city_id);
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Division $division): bool
    {
        if ($user->isManager()) {
            return $this->userManagesCity($user, $division->city_id);
        }

        return false;
    }

    /**
     * Check if user manages the given city.
     */
    private function userManagesCity(User $user, ?int $cityId): bool
    {
        if ($cityId === null) {
            return false;
        }

        return $user->managedCities()->where('cities.id', $cityId)->exists();
    }
}
