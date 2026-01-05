<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'gm_id',
    ];

    /**
     * Get the general manager (GM) of the division.
     */
    public function gm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_id');
    }

    /**
     * Get the departments for the division.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
