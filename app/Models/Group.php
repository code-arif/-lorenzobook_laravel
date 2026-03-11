<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'image_url',
        'group_type',
        'created_by',
        'last_activity_at',
        'is_active',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'is_active'        => 'boolean',
    ];

    public function getImageUrlAttribute($value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        return $value ? url($value) : null;
    }

    // Group.php
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
            ->withPivot([
                'role',
                'is_muted',
                'is_banned',
                'is_kicked',
                'is_left',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'group_id');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Chat::class, 'group_id')->latest()->limit(1);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('members', fn($q) => $q->where('user_id', $userId));
    }
}
