<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = ['api', 'web'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile_number',
        'password',
        'otp',
        'cover',
        'otp_expires_at',
        'last_activity_at'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];



    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'otp_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getCoverAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }

    public function getIsOnlineAttribute()
    {
        return $this->last_activity_at > now()->subMinutes(5);
    }

    public function getRoleAttribute()
    {
        return  $this->getRoleNames()->first();
    }

    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseTokens::class);
    }

    //chat model relation
    public function senders()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function receivers()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function roomsAsUserOne()
    {
        return $this->hasMany(Room::class, 'user_one_id');
    }

    public function roomsAsUserTwo()
    {
        return $this->hasMany(Room::class, 'user_two_id');
    }

    public function allRooms()
    {
        return Room::where('user_one_id', $this->id)->orWhere('user_two_id', $this->id);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }


    // group model relation
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    // channel model relation
    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'channel_members')
            ->withPivot('role', 'joined_at', 'left_at', 'is_active')
            ->withTimestamps();
    }


    // get the users you have added
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id');
    }

    // get the users who have added you
    public function friendOf()
    {
        return $this->belongsToMany(User::class, 'friends', 'friend_id', 'user_id');
    }
}
