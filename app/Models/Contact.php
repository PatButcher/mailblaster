<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'email', 'first_name', 'last_name', 'company',
        'phone', 'tags', 'subscribed', 'source',
        'unsubscribed_at', 'bounce_count'
    ];

    protected $casts = [
        'subscribed' => 'boolean',
        'unsubscribed_at' => 'datetime',
        'bounce_count' => 'integer',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->email;
    }

    public function getTagsArrayAttribute(): array
    {
        if (empty($this->tags)) return [];
        return array_map('trim', explode(',', $this->tags));
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'recipient_email', 'email');
    }

    public function mailingLists()
    {
        return $this->belongsToMany(MailingList::class, 'contact_mailing_list');
    }
}