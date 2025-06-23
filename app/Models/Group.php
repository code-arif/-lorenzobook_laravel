<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{


    protected $fillable = [
        'name',
        'image_url',
        'group_type',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    // members
    // Group.php

    // Group.php
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }




    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
