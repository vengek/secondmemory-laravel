<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDO;

class Items extends Model
{
    const NUMBER_FOR_LEARN = 1;

    const TYPE_TO_REPEAT = 0;
    const TYPE_TO_LEARN = 1;
    const TYPE_INACTIVE = 2;

    protected $fillable = ['id', 'type', 'title', 'text', 'href', 'user_id'];

    public function scopeByUserId($query, $userId)
    {
        return $query->where('user_id', '=', $userId);
    }

    public function save(array $options = [])
    {
        Link::where('id', $this->id)->where('type_id', Link::TYPE_EMBED)->delete();
        preg_match_all('~data-sm-id="(\d+)"~', $this->text, $matches);
        foreach (array_unique($matches[1]) as $match) {
            $link = new Link();
            $link->id = $this->id;
            $link->type_id = Link::TYPE_EMBED;
            $link->right = (int)$match;
            $link->save();
        }
        return parent::save($options);
    }

    public static function getItemForLearn($userId)
    {
        return self::orderByRaw('RANDOM()')
            ->where('type', '=', Items::TYPE_TO_LEARN)
            ->byUserId($userId)
            ->take(Items::NUMBER_FOR_LEARN)
            ->first();
    }

    public static function getInactiveItem($userId)
    {
        return self::orderByRaw('RANDOM()')
            ->where('type', '=', Items::TYPE_INACTIVE)
            ->byUserId($userId)
            ->take(1)
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
        return $item ? new self($item) : null;
    }

    public static function searchItemsForUser($query, $userId)
    {
        return self::whereRaw("text @@ ?", [$query])
            ->orWhereRaw("title @@ ?", [$query])
            ->orWhereRaw("href @@ ?", [$query])
            ->byUserId($userId)
            ->get();
    }
}
