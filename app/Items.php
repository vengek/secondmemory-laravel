<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDO;

class Items extends Model
{
    const NUMBER_FOR_LEARN = 1;
    const TYPE_TO_LEARN = 1;

    protected $fillable = ['id', 'type', 'text', 'href', 'user_id'];

    public function scopeByUserId($query, $userId)
    {
        return $query->where('user_id', '=', $userId);
    }

    public static function getItemForLearn($userId)
    {
        return self::orderByRaw('RANDOM()')
                ->where('type', '=', Items::TYPE_TO_LEARN)
                ->byUserId($userId)
                ->take(Items::NUMBER_FOR_LEARN)
                ->first();
    }

    public static function toRepeat()
    {
        DB::setFetchMode(PDO::FETCH_ASSOC);
        $item = DB::table('items')
            ->select('items.*')
            ->leftJoin('repeat_queue', 'items.id', '=', 'repeat_queue.id')
            ->whereRaw('COALESCE(repeat_queue.next_repeat, to_timestamp(0)) < NOW() AND TYPE=0')
            ->where('items.user_id', '=', $_SESSION['userId'])
            ->orderByRaw('COALESCE(repeat_queue.next_repeat, to_timestamp(0)) ASC')
            ->take(1)
            ->first();
        DB::setFetchMode(PDO::FETCH_CLASS);
        return new self($item);
    }

    public static function searchItemsForUser($query, $userId)
    {
        return self::whereRaw("text @@ ?", [$query])->orWhereRaw("href @@ ?", [$query])->byUserId($userId)->get();
    }
}
