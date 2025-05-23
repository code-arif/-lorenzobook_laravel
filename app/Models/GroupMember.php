<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'is_muted',
        'is_banned',
        'is_kicked',
        'is_left',
        'is_joined',
        'is_invited',
        'is_requested',
        'is_blocked',
        'is_reported',
        'is_favorite',
        'is_archived',
        'is_pinned',
        'is_silenced',
        'is_verified',
        'is_online',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
