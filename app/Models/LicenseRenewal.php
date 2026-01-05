<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class LicenseRenewal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'license_id',
        'new_expiry_date',
        'renewal_cost',
        'renewed_by',
        'notes',
        'renewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_expiry_date' => 'date',
            'new_expiry_date' => 'date',
            'renewal_cost' => 'decimal:2',
            'renewed_at' => 'datetime',
        ];
    }

    /**
     * Get the user license that owns the renewal.
     */
    /**
     * Get the license that this renewal belongs to.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    public function renewer()
    {
        // Assuming your User model is App\Models\User
        // and the foreign key is 'renewed_by'
        return $this->belongsTo(User::class, 'renewed_by');
    }

    /**
     * Compatibility accessors to match existing view field names.
     * - `renewal_date` maps to `renewed_at`
     * - `cost` maps to `renewal_cost`
     */
    public function getRenewalDateAttribute()
    {
        return $this->renewed_at;
    }

    public function getCostAttribute()
    {
        return $this->renewal_cost;
    }
}
