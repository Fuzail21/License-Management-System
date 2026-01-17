<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'license_name',
        'renewal_type',
        'renewal_cycle',
        'number_license_assigned',
        'version',
        'max_users',
        'cost',
        'renewal_date',
        'description',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'max_users' => 'integer',
            'license_type' => LicenseType::class,
            'status' => LicenseStatus::class,
            'approved_at' => 'datetime',
            'renewal_date' => 'date',
        ];
    }

    /**
     * Get the vendor that owns the license.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user licenses for the license.
     */
    public function userLicenses(): HasMany
    {
        return $this->hasMany(UserLicense::class);
    }

    /**
     * Get the user who created this license.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved/rejected this license.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include approved licenses.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', LicenseStatus::Approved);
    }

    /**
     * Scope a query to only include pending licenses.
     */
    public function scopePending($query)
    {
        return $query->where('status', LicenseStatus::Pending);
    }

    /**
     * Scope a query to only include rejected licenses.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', LicenseStatus::Rejected);
    }

    /**
     * Check if license is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === LicenseStatus::Pending;
    }

    /**
     * Check if license is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === LicenseStatus::Approved;
    }

    /**
     * Check if license is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === LicenseStatus::Rejected;
    }
}

enum LicenseType: string
{
    case Subscription = 'subscription';
    case Perpetual = 'perpetual';
}

enum LicenseStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
