<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['user_one_id','user_two_id','user_one_muted_until','user_two_muted_until','user_one_deleted_at','user_two_deleted_at'];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
    
}
