<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;

class EmailLog extends Model
{
    use HasFactory;
    // , SoftDeletes;

    protected $fillable = [
        'campaign_id', 'smtp_provider_id', 'recipient_email', 'recipient_name',
        'subject', 'status', 'attempts', 'error_message', 'smtp_log',
        'message_id', 'smtp_response_code', 'smtp_banner',
        'is_single_send', 'sent_at', 'failed_at'
    ];

    protected $casts = [
        'sent_at'        => 'datetime',
        'failed_at'      => 'datetime',
        'attempts'       => 'integer',
        'is_single_send' => 'boolean',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function smtpProvider()
    {
        return $this->belongsTo(SmtpProvider::class);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'sent'      => 'green',
            'failed'    => 'red',
            'queued'    => 'blue',
            'sending'   => 'yellow',
            'paused'    => 'orange',
            'cancelled' => 'gray',
            default     => 'gray'
        };
    }
}