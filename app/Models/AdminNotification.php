<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $table = 'admin_notifications';

    protected $fillable = [
        'type', 'title', 'message', 'icon', 'color', 'link', 'read', 'data'
    ];

    protected $casts = [
        'read' => 'boolean',
        'data' => 'array',
    ];

    public static function recordNotification(
        string $type,
        string $title,
        string $message,
        string $icon = 'bell',
        string $color = 'blue',
        ?string $link = null,
        array $data = []
    ): self {
        return self::create([
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'icon'    => $icon,
            'color'   => $color,
            'link'    => $link,
            'read'    => false,
            'data'    => $data,
        ]);
    }

    public static function unreadCount(): int
    {
        return self::where('read', false)->count();
    }
}