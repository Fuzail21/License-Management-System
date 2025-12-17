<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFeedback extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'satisfied', 'issue_type', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

