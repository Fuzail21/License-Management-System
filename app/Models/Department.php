<?php

namespace App\Models;

use App\Traits\HasCityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, HasCityScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'city_id',
        'name',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DepartmentStatus::class,
        ];
    }

    /**
     * Get the city that the department belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the divisions for the department.
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Get the employees for the department (through divisions).
     */
    public function employees()
    {
        return $this->hasManyThrough(Employee::class, Division::class);
    }

    /**
     * Scope a query to only include active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive departments.
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
     * Check if department is active.
     */
    public function isActive(): bool
    {
        return $this->status === DepartmentStatus::Active;
    }

    /**
     * Check if department is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === DepartmentStatus::Inactive;
    }
}

enum DepartmentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
