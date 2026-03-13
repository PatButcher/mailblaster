<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedContact extends Model
{
    protected $fillable = [
        'email',
        'reason',
        'blocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
    ];
}
