<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelMessage extends Model
{
    protected $fillable = [
        'channel_id',
        'user_id',
        'message',
        'sent_at',
        'is_read',
        'is_deleted',
        'deleted_at',
        'attachment_url',
        'attachment_type',
        'attachment_name',
        'attachment_size',
        'attachment_mime_type',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
