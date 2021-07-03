<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class LinkController extends Controller
{
    protected $user;

    public function __construct()
    {
    $this->user = auth()->user();
    }

    public function saveLink(Request $request){
        // common saving code
        $link_id = $request->link_id;
        if(!empty($link_id)){
            $link_model = Link::find($link_id);
        }else{
            $link_model = new Link();
            $link_model->user_id = $this->user->id;
        }

        // save title if exist
        if($request->has('title'))
            $link_model->title = $request->title;

        // save link if exist
        if($request->has('link_url'))
            $link_model->link_url = $request->link_url;

        // i assume 0- deactivate 1- activate
        if($request->has('active'))
            $link_model->active = $request->active;

        // save file if exist
        if($request->hasFile('link_image')){
            $image = $request->file('link_image');
            $file_name = time() . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public', $file_name);
            $link_model->photo = $file_name;
        }
        $link_model->save();
        return response(json_encode(['status' => 200, 'message' => 'Details Has been saved']));
    }

    public function linkUserLinks(Request $request){
        $project_url = env('APP_URL') . '/public/';
        $allLinks = Link::where('user_id', $this->user->id)
        ->orderBy('sort_order')->get();
        return response(json_encode(['links' => $allLinks]));
    }

    public function delete_link(Request $request){
        $link_id = $request->link_id;
        $delete_success = Link::where('id', $link_id)->where('user_id', $this->user->id)->delete();
        return response(json_encode(['status' => 200, 'message' => (($delete_success)? 'link deleted successfully': 'something went wrong')]));
    }
}
