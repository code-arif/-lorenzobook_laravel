<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_url',
        'channel_type',
        'invite_token',
        'description',
        'is_active',
        'is_archived',
        'archived_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'channel_members', 'channel_id', 'user_id');
    }
}
