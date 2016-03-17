<?php

namespace App\Http\Controllers;

use App\Items;
use Carbon\Carbon;
use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;


class ItemsController extends Controller
{

    public function __construct()
    {
        $this->middleware('cors');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Items::all();
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
        try {
            $item->save();
            return ['code' => 200, 'message' => 'created', 'description' => 'Added item','item' => $item];
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
            return ['code' => 200, 'message' => 'ok', 'description' => 'Item','item' => $item];
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
            return ['code' => 200, 'message' => 'ok', 'description' => 'Updated item','item' => $item];
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
        return Items::orderByRaw('RANDOM()')->where('type', '=', Items::TYPE_TWO)->take(Items::NUMBER_FOR_LEARN)->get();
    }

    public function search($query)
    {
        $items =  Items::whereRaw("text @@ ?", [$query])->orWhereRaw("href @@ ?", [$query])->get();
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
            if (DB::table('repeat_queue')->where('id', '=', $id)->get()) {
                DB::table('repeat_queue')->update(['id' => $id, 'next_repeat' => $nextDateToRepeat]);
            } else {
                DB::table('repeat_queue')->insert(['id' => $id, 'next_repeat' => $nextDateToRepeat]);
            }
            return ;
        } catch (\Exception $e) {

        }
    }

    public function next_to_repeat()
    {
        $item = DB::table('items')
            ->leftJoin('repeat_queue', 'items.id', '=', 'repeat_queue.id')
            ->whereRaw('NULLIF(repeat_queue.next_repeat, to_timestamp(0)) < NOW() AND TYPE=0')
            ->orderByRaw('NULLIF(repeat_queue.next_repeat, to_timestamp(0)) ASC')
            ->take(1)
            ->get();
        return ['code' => 200, 'message' => 'ok', 'item' => $item];
    }
}
