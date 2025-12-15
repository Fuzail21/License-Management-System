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
        'user_license_id',
        'old_expiry_date',
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
    public function userLicense(): BelongsTo
    {
        return $this->belongsTo(UserLicense::class);
    }

    public function renewer()
    {
        // Assuming your User model is App\Models\User
        // and the foreign key is 'renewed_by'
        return $this->belongsTo(User::class, 'renewed_by');
    }
}
