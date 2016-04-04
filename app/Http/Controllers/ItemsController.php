<?php
namespace App\Http\Controllers;

use App\Items;
use App\Link;
use App\RepeatLog;
use App\RepeatQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Requests;

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
        return $item;
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
            return $item;
        } catch (ModelNotFoundException $e) {
            return response($e->getMessage(), 404);
        }
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
            return $item;
        } catch (ModelNotFoundException $e) {
            return response($e->getMessage(), 404);
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
            Link::where('id', $id)->delete();
            Link::where('right', $id)->delete();
            Link::where('type_id', $id)->delete();
            return response('', 200);
        } catch (ModelNotFoundException $e) {
            return response($e->getMessage(), 404);
        }
    }

    public function learn()
    {
        if ($item = Items::getItemForLearn($_SESSION['userId'])) {
            return $item;
        } else {
            return response('', 204);
        }
    }

    public function search($query)
    {
        $items =  Items::searchItemsForUser($query, $_SESSION['userId']);
        return $items;
    }

    public function repeat($id)
    {
        try {
            $item = Items::findOrFail($id);
            RepeatLog::insert(['id' => $id, 'repeated_at' => 'NOW()']);
            RepeatQueue::setNextRepeat($id, RepeatLog::where('id', $id)->count());
            return $item;
        } catch (ModelNotFoundException $e) {
            return response($e->getMessage(), 404);
        }
    }

    public function next_to_repeat()
    {
        $item = Items::toRepeat();
        if ($item) {
            return $item;
        } else {
            return response('', 204);
        }
    }
    
    public function get_links($id)
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

    public function delete_link(Request $request, $id)
    {
        $right = $request->input('right');
        $typeId = $request->input('type_id', 0);
        Link::where('id', $id)->where('right', $right)->where('type_id', $typeId)->delete();
        Link::where('id', $right)->where('right', $id)->where('type_id', $typeId)->delete();
        return response('', 200);
    }
}
