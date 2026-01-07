<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Department;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'status',
        'password',
        'can_create_license',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'can_create_license' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the role that the user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }
    
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->name, $roles);
    }

    /**
     * Get the cities that this user manages (many-to-many).
     */
    public function managedCities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'city_managers')
            ->withTimestamps()
            ->withPivot('assigned_at');
    }

    /**
     * Check if the user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role && $this->role->name === 'Manager';
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'Admin';
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the user licenses for the user.
     */
    public function userLicenses(): HasMany
    {
        return $this->hasMany(UserLicense::class);
    }

    /**
     * Get the divisions managed by this user (as GM).
     */
    public function managedDivisions(): HasMany
    {
        return $this->hasMany(Division::class, 'gm_id');
    }

    /**
     * Get licenses created by this user.
     */
    public function createdLicenses(): HasMany
    {
        return $this->hasMany(License::class, 'created_by');
    }

    /**
     * Get licenses approved/rejected by this user.
     */
    public function approvedLicenses(): HasMany
    {
        return $this->hasMany(License::class, 'approved_by');
    }

    /**
     * Get pending licenses count for this manager.
     */
    public function getPendingLicensesCount(): int
    {
        return License::where('created_by', $this->id)
            ->where('status', 'pending')
            ->count();
    }
}

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
}
