<?php

namespace App\Models;

use App\Traits\HasCityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory, HasCityScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'name',
        'status',
    ];

    /**
     * Get the department that the division belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employees for the division.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => DivisionStatus::class,
        ];
    }

    /**
     * Scope a query to only include active divisions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive divisions.
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
     * Check if division is active.
     */
    public function isActive(): bool
    {
        return $this->status === DivisionStatus::Active;
    }

    /**
     * Check if division is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === DivisionStatus::Inactive;
    }
}

enum DivisionStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
