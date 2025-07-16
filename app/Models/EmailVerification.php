<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'is_verified'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean'
    ];

    public static function generateCode($email)
    {
        // Delete existing codes for this email
        self::where('email', $email)->delete();
        
        // Generate 6-digit code
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Create new verification record
        return self::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(15), // 15 minutes expiry
            'is_verified' => false
        ]);
    }

    public function isExpired()
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    public function isValid()
    {
        return !$this->is_verified && !$this->isExpired();
    }

    public static function verifyCode($email, $code)
    {
        $verification = self::where('email', $email)
            ->where('code', $code)
            ->first();

        if (!$verification || !$verification->isValid()) {
            return false;
        }

        $verification->update(['is_verified' => true]);
        return true;
    }
}
