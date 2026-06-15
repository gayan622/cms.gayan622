<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name', 'email', 'subject', 'message', 'ip_address',
        'is_read', 'is_replied', 'reply', 'replied_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'is_replied' => 'boolean',
        'replied_at' => 'datetime',
    ];
}
