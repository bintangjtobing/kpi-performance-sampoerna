<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_progress_id',
        'item_name',
        'target_value',
        'actual_value',
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];

    public function dailyProgress()
    {
        return $this->belongsTo(DailyProgress::class);
    }
}
