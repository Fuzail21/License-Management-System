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
        'license_type',
        'version',
        'max_users',
        'cost',
        'description',
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
}

enum LicenseType: string
{
    case Subscription = 'subscription';
    case Perpetual = 'perpetual';
}
