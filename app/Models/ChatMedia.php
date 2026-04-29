<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'file',
    ];

    public function getFileAttribute($value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        return $value ? url($value) : null;
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
