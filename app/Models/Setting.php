<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    protected $fillable = [
        'app_name',
        'app_logo',
        // 'primary_color',
        // 'secondary_color',
        // 'currency_name',
        // 'currency_symbol',
        'support_email',
        // 'support_phone',
        'address',
        'footer_text',
        // 'timezone',
    ];

    /**
     * Get the settings instance (singleton pattern for single row).
     */
    public static function getSettings()
    {
        return self::first() ?? new self();
    }

    /**
     * Get the logo URL if it exists, otherwise return null.
     */
    public function getLogoUrlAttribute()
    {
        if (empty($this->app_logo)) {
            return null;
        }

        try {
            if (Storage::disk('public')->exists($this->app_logo)) {
                return Storage::url($this->app_logo);
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return null;
    }

    /**
     * Check if logo exists.
     */
    public function hasLogo()
    {
        if (empty($this->app_logo)) {
            return false;
        }

        try {
            return Storage::disk('public')->exists($this->app_logo);
        } catch (\Exception $e) {
            return false;
        }
    }
}
