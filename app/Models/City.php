<?php

namespace App\Models;

use App\Traits\HasCityScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class City extends Model
{
    use HasCityScope;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the divisions for the city.
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Get the managers for the city (many-to-many).
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'city_managers')
            ->withTimestamps()
            ->withPivot('assigned_at');
    }

    /**
     * Scope a query to only include active cities.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive cities.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if city is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if city is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }
}
