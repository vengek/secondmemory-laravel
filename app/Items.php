<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    const NUMBER_FOR_LEARN = 5;
    const TYPE_TO_LEARN = 1;

    protected $fillable = ['type', 'text', 'href', 'user_id'];

    public function scopeByUserId($query, $userId)
    {
        return $query->where('user_id', '=', $userId);
    }
}
