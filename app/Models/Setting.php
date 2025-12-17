<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    protected $fillable = [
        'app_name',
        'app_logo',
        'primary_color',
        'secondary_color',
        'currency_name',
        'currency_symbol',
        'support_email',
        'support_phone',
        'address',
        'footer_text',
        'timezone',
    ];

    /**
     * Get the settings instance (singleton pattern for single row).
     */
    public static function getSettings()
    {
        return self::first() ?? new self();
    }
}
