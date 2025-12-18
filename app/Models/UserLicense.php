<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserLicense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'license_id',
        'assigned_date',
        'renewal_cost',
        'renewed_at',
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
            'assigned_date' => 'date',
            'start_date' => 'date',
            'expiry_date' => 'date',
            'renewal_cost' => 'decimal:2',
            'renewed_at' => 'datetime',
            'renewal_cycle' => RenewalCycle::class,
            'status' => UserLicenseStatus::class,
        ];
    }

    /**
     * Get the user that owns the license assignment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the license for the user license.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Get the renewals for the user license.
     */
    public function renewals(): HasMany
    {
        return $this->hasMany(LicenseRenewal::class);
    }
}

enum RenewalCycle: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';
    case Perpetual = 'perpetual';
}

enum UserLicenseStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Suspended = 'suspended';
}
