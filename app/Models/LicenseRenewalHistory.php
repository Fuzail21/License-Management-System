<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseRenewalHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'license_id',
        'old_renewal_date',
        'new_renewal_date',
        'change_type',
        'changed_by',
        'reason',
        'old_status',
        'new_status',
        'renewal_cost',
        'metadata',
        'changed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_renewal_date' => 'date',
            'new_renewal_date' => 'date',
            'change_type' => RenewalChangeType::class,
            'renewal_cost' => 'decimal:2',
            'metadata' => 'array',
            'changed_at' => 'datetime',
        ];
    }

    /**
     * Get the license that this history belongs to.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Get the user who made the change.
     */
    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope a query to order by most recent first.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('changed_at', 'desc');
    }

    /**
     * Scope a query to filter by change type.
     */
    public function scopeOfType($query, string|RenewalChangeType $type)
    {
        $typeValue = $type instanceof RenewalChangeType ? $type->value : $type;
        return $query->where('change_type', $typeValue);
    }

    /**
     * Get the change type label for display.
     */
    public function getChangeTypeLabelAttribute(): string
    {
        return match ($this->change_type) {
            RenewalChangeType::Renewal => 'Renewal',
            RenewalChangeType::Extension => 'Extension',
            RenewalChangeType::Correction => 'Correction',
            RenewalChangeType::Initial => 'Initial Setup',
            default => ucfirst($this->change_type->value ?? 'Unknown'),
        };
    }

    /**
     * Get the badge color class based on change type.
     */
    public function getChangeTypeBadgeClassAttribute(): string
    {
        return match ($this->change_type) {
            RenewalChangeType::Renewal => 'bg-green-100 text-green-800',
            RenewalChangeType::Extension => 'bg-blue-100 text-blue-800',
            RenewalChangeType::Correction => 'bg-yellow-100 text-yellow-800',
            RenewalChangeType::Initial => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}

/**
 * Enum for renewal change types.
 */
enum RenewalChangeType: string
{
    case Renewal = 'renewal';
    case Extension = 'extension';
    case Correction = 'correction';
    case Initial = 'initial';
}
