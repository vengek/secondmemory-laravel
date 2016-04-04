<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class RepeatQueue extends Model
{
    protected $table = 'repeat_queue';
    protected $fillable = ['id', 'next_repeat'];
    public $timestamps = false;

    public static function setNextRepeat($id, $numberOfRepeats)
    {
        $nextDateToRepeat = Carbon::now();
        switch ($numberOfRepeats) {
            case 0:
                $nextDateToRepeat->addDay();
                break;
            case 1:
                $nextDateToRepeat->addWeeks(2);
                break;
            case 2:
                $nextDateToRepeat->addMonths(2);
                break;
            default:
                $nextDateToRepeat->addYear();
                break;
        }
        self::updateOrCreate(['id' => $id], ['next_repeat' => $nextDateToRepeat]);
    }
}
