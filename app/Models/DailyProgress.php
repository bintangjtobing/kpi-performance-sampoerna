<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProgress extends Model
{
    use HasFactory;

    protected $table = 'daily_progress';

    protected $fillable = [
        'user_id',
        'progress_date',
        'overall_percentage',
        'photos',
    ];

    protected $casts = [
        'progress_date' => 'date',
        'overall_percentage' => 'decimal:2',
        'photos' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progressItems()
    {
        return $this->hasMany(ProgressItem::class);
    }
}
