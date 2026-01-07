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

            if ($table === 'departments') {
                $builder->whereIn('city_id', $managedCityIds);
            } elseif ($table === 'divisions') {
                $builder->whereHas('department', function ($query) use ($managedCityIds) {
                    $query->whereIn('city_id', $managedCityIds);
                });
            } elseif ($table === 'employees') {
                $builder->whereHas('division.department', function ($query) use ($managedCityIds) {
                    $query->whereIn('city_id', $managedCityIds);
                });
            }
        } else {
            $builder->whereRaw('1 = 0');
        }
    }
}
