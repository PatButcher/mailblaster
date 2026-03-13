<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SingleSendToken extends Model
{
    protected $fillable = [
        'token', 'to_email', 'to_name', 'subject',
        'body_html', 'body_text', 'smtp_provider_id',
        'status', 'result_log'
    ];

    public function smtpProvider()
    {
        return $this->belongsTo(SmtpProvider::class);
    }
}