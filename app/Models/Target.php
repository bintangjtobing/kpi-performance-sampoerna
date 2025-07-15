<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'target_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
