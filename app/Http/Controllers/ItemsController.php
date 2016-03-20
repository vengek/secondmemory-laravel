<?php

namespace App\Http\Controllers;

use App\Items;
use Carbon\Carbon;
use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;

use Symfony\Component\HttpFoundation\JsonResponse;


class ItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Items::byUserId($_SESSION['userId'])->get();
        return $items;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $item = new Items();
        $item->fill($request->all());
        $item->user_id = $_SESSION['userId'];
        try {
            $item->save();
            return ['code' => 200, 'message' => 'created', 'description' => 'Added item','items' => $item];
        } catch (\Exception $e) {
            return ['code' => 500];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $item = Items::findOrFail($id);
            return ['code' => 200, 'message' => 'ok', 'description' => 'Item','items' => $item];
        } catch (ModelNotFoundException $e) {
            return ['code' => 404, 'message' => $e->getMessage()];
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $item = Items::findOrFail($id);
            $item->fill($request->all());
            $item->save();
            return ['code' => 200, 'message' => 'ok', 'description' => 'Updated item','items' => $item];
        } catch (ModelNotFoundException $e) {
            return ['code' => 404, 'message' => $e->getMessage()];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Items::findOrFail($id)->delete();
            return ['code' => 200, 'message' => 'deleted',];
        } catch (ModelNotFoundException $e) {
            return ['code' => 404, 'message' => $e->getMessage()];
        }
    }

    public function learn()
    {
        return Items::orderByRaw('RANDOM()')->where('type', '=', Items::TYPE_TWO)->byUserId($_SESSION['userId'])->take(Items::NUMBER_FOR_LEARN)->get();
    }

    public function search($query)
    {
        $items =  Items::whereRaw("text @@ ?", [$query])->orWhereRaw("href @@ ?", [$query])->byUserId($_SESSION['userId'])->get();
        return ['code' => 200, 'message' => 'ok', 'items' => $items];
    }

    public function repeat($id)
    {
        try {
            $item = Items::findOrFail($id);
            $nextDateToRepeat = Carbon::now();
            $repeatLog = DB::table('repeat_log');
            $numberOfRepeats = $repeatLog
                ->where('id', '=', $id)
                ->count();
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
            $repeatLog->insert(['id' => $id, 'repeated_at' => 'NOW()']);
            $repeatQue = DB::table('repeat_queue');
            if ($repeatQue->where('id', '=', $id)->get()) {
                $repeatQue
                    ->where('id', '=', $id)
                    ->update(['next_repeat' => $nextDateToRepeat]);
            } else {
                $repeatQue>insert(['id' => $id, 'next_repeat' => $nextDateToRepeat]);
            }
            return $item;
        } catch (\Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    public function next_to_repeat()
    {
        $item = DB::table('items')
            ->leftJoin('repeat_queue', 'items.id', '=', 'repeat_queue.id')
            ->whereRaw('NULLIF(repeat_queue.next_repeat, to_timestamp(0)) < NOW() AND TYPE=0')
            ->where('items.user_id', '=', $_SESSION['userId'])
            ->orderByRaw('NULLIF(repeat_queue.next_repeat, to_timestamp(0)) ASC')
            ->take(1)
            ->get();
        return ['code' => 200, 'message' => 'ok', 'items' => $item];
    }
}
