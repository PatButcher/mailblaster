<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'subject', 'from_name', 'from_email', 'reply_to',
        'body_html', 'body_text', 'recipient_filter', 'tags_filter',
        'status', 'scheduled_at', 'started_at', 'completed_at',
        'total_recipients', 'batch_size', 'delay_between_batches', 'created_by',
        'duplicated_from', 'is_recurring', 'mailing_list_id'
    ];

    protected $casts = [
        'scheduled_at'   => 'datetime',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'total_recipients' => 'integer',
        'batch_size'     => 'integer',
        'delay_between_batches' => 'integer',
        'is_recurring'   => 'boolean',
    ];

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function duplicatedFrom()
    {
        return $this->belongsTo(Campaign::class, 'duplicated_from');
    }

    public function duplicates()
    {
        return $this->hasMany(Campaign::class, 'duplicated_from');
    }

    public function mailingList()
    {
        return $this->belongsTo(MailingList::class);
    }

    public function getSentCountAttribute(): int
    {
        return $this->emailLogs()->where('status', 'sent')->count();
    }

    public function getFailedCountAttribute(): int
    {
        return $this->emailLogs()->where('status', 'failed')->count();
    }

    public function getQueuedCountAttribute(): int
    {
        return $this->emailLogs()->where('status', 'queued')->count();
    }

    public function getProgressPercentAttribute(): float
    {
        if (!$this->total_recipients) return 0;
        $processed = $this->emailLogs()->whereIn('status', ['sent', 'failed'])->count();
        return round(($processed / $this->total_recipients) * 100, 1);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'gray',
            'queued'    => 'blue',
            'sending'   => 'yellow',
            'completed' => 'green',
            'paused'    => 'orange',
            'cancelled' => 'red',
            default     => 'gray'
        };
    }
}