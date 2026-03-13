<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmtpProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'host', 'port', 'username', 'password',
        'encryption', 'from_email', 'from_name',
        'max_daily_emails', 'daily_sent_count', 'total_sent_count',
        'failed_count', 'priority', 'active',
        'daily_reset_at', 'last_tested_at', 'test_status'
    ];

    protected $casts = [
        'active' => 'boolean',
        'max_daily_emails' => 'integer',
        'daily_sent_count' => 'integer',
        'total_sent_count' => 'integer',
        'failed_count' => 'integer',
        'priority' => 'integer',
        'daily_reset_at' => 'datetime',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = ['password'];

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function getRemainingTodayAttribute(): int
    {
        return max(0, $this->max_daily_emails - $this->daily_sent_count);
    }

    public function getUsagePercentAttribute(): float
    {
        if ($this->max_daily_emails === 0) return 0;
        return round(($this->daily_sent_count / $this->max_daily_emails) * 100, 1);
    }

    public function getIsAtLimitAttribute(): bool
    {
        return $this->daily_sent_count >= $this->max_daily_emails;
    }
}