<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'name',
        'email',
        'phone',
        'designation',
        'status',
        'head',
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
            'head' => 'boolean',
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
}
