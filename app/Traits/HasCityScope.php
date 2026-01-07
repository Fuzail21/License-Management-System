<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCityScope
{
    /**
     * Scope query to only include records accessible by the manager through their assigned cities.
     * Admin users bypass this scoping.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeForManager(Builder $query): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isManager()) {
            $managedCityIds = $user->managedCities()->pluck('cities.id')->toArray();

            if (empty($managedCityIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $this->applyCityScope($query, $managedCityIds);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Apply city scoping based on model type.
     * Override this method in models with different scoping logic.
     *
     * @param Builder $query
     * @param array $managedCityIds
     * @return Builder
     */
    protected function applyCityScope(Builder $query, array $managedCityIds): Builder
    {
        $table = $this->getTable();

        // Cities: Managers can only access their assigned cities
        if ($table === 'cities') {
            return $query->whereIn('id', $managedCityIds);
        }

        // Departments: Scoped by city_id
        if ($table === 'departments') {
            return $query->whereIn('city_id', $managedCityIds);
        }

        // Divisions: Scoped through department's city_id
        if ($table === 'divisions') {
            return $query->whereHas('department', function ($q) use ($managedCityIds) {
                $q->whereIn('city_id', $managedCityIds);
            });
        }

        // Employees: Scoped through division → department → city
        if ($table === 'employees') {
            return $query->whereHas('division.department', function ($q) use ($managedCityIds) {
                $q->whereIn('city_id', $managedCityIds);
            });
        }

        return $query;
    }
}
