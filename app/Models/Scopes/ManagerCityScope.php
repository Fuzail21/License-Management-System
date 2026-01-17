<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ManagerCityScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * This global scope automatically filters queries for managers
     * to only include records within their assigned cities.
     *
     * Admin users bypass this scope entirely.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (!$user) {
            $builder->whereRaw('1 = 0');
            return;
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isManager()) {
            $managedCityIds = $user->managedCities()->pluck('cities.id')->toArray();

            if (empty($managedCityIds)) {
                $builder->whereRaw('1 = 0');
                return;
            }

            $table = $model->getTable();

            // New hierarchy: City -> Division -> Department -> Employee
            if ($table === 'divisions') {
                // Divisions belong directly to cities
                $builder->whereIn('city_id', $managedCityIds);
            } elseif ($table === 'departments') {
                // Departments belong to divisions, which belong to cities
                $builder->whereHas('division', function ($query) use ($managedCityIds) {
                    $query->whereIn('city_id', $managedCityIds);
                });
            } elseif ($table === 'employees') {
                // Employees belong to departments -> divisions -> cities
                $builder->whereHas('department.division', function ($query) use ($managedCityIds) {
                    $query->whereIn('city_id', $managedCityIds);
                });
            }
        } else {
            $builder->whereRaw('1 = 0');
        }
    }
}
