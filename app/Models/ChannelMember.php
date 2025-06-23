<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelMember extends Model
{
    protected $fillable = [
        'channel_id',
        'user_id',
        'role',
        'joined_at',
        'left_at',
        'is_active',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'is_active' => 'boolean',
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
