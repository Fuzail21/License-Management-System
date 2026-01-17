<?php

namespace App\Models;

use App\Traits\HasCityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Employee extends Model
{
    use HasFactory, HasCityScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'employee_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'hire_date',
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
            'status' => EmployeeStatus::class,
            'hire_date' => 'date',
        ];
    }

    /**
     * Get the department that the employee belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the division through the department.
     */
    public function division(): HasOneThrough
    {
        return $this->hasOneThrough(
            Division::class,
            Department::class,
            'id',            // Foreign key on departments table
            'id',            // Foreign key on divisions table
            'department_id', // Local key on employees table
            'division_id'    // Local key on departments table
        );
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the user licenses for the employee.
     */
    public function userLicenses(): HasMany
    {
        return $this->hasMany(UserLicense::class);
    }

    /**
     * Get the feedback submitted by the employee.
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(UserFeedback::class, 'employee_id');
    }

    /**
     * Get the license renewals performed by this employee.
     */
    public function renewals(): HasMany
    {
        return $this->hasMany(LicenseRenewal::class, 'renewed_by_employee_id');
    }
}

enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Terminated = 'terminated';
    case OnLeave = 'on_leave';
}
