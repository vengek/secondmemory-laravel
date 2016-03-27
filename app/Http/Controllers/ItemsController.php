<?php
namespace App\Http\Controllers;

use App\Items;
use App\Link;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

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
        
        $item->save();
        return ['code' => 200, 'message' => 'created', 'description' => 'Added item','item' => $item];
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
        $item = Items::orderByRaw('RANDOM()')
            ->where('type', '=', Items::TYPE_TO_LEARN)
            ->byUserId($_SESSION['userId'])
            ->take(Items::NUMBER_FOR_LEARN)
            ->first();

        if ($item) {
            return $item;
        } else {
            return response('', 204);
        }
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
            $repeatQueue = DB::table('repeat_queue');
            if ($repeatQueue->where('id', '=', $id)->get()) {
                $repeatQueue
                    ->where('id', '=', $id)
                    ->update(['next_repeat' => $nextDateToRepeat]);
            } else {
                $repeatQueue->insert(['id' => $id, 'next_repeat' => $nextDateToRepeat]);
            }
            return $item;
        } catch (\Exception $e) {
            return response($e->getMessage(), 404);
        }
    }

    public function next_to_repeat()
    {
        $item = DB::table('items')
            ->select('items.*')
            ->leftJoin('repeat_queue', 'items.id', '=', 'repeat_queue.id')
            ->whereRaw('COALESCE(repeat_queue.next_repeat, to_timestamp(0)) < NOW() AND TYPE=0')
            ->where('items.user_id', '=', $_SESSION['userId'])
            ->orderByRaw('COALESCE(repeat_queue.next_repeat, to_timestamp(0)) ASC')
            ->take(1)
            ->first();
        if ($item) {
            return ['code' => 200, 'message' => 'ok', 'item' => $item];
        } else {
            return response('', 204);
        }
    }
    
    public function get_links(Request $request, $id)
    {
	return Link::where('id', $id)->get();
    }
    
    public function put_links(Request $request, $id)
    {
	$data = [
	    'id' => $id,
	    'right' => $request->input('right'),
	    'type_id' => $request->input('type_id', 0),
	];
	$link = Link::firstOrNew($data);
	$link->x = $request->input('x', 0);
	$link->y = $request->input('y', 0);
	$link->save();
	
	list($data['id'], $data['right']) = [$data['right'], $data['id']];
	$link = Link::firstOrNew($data);
	$link->save();
	
	return response('', 200);
    }
}
