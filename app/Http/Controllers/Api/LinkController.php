<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class LinkController extends Controller
{
    public function saveLink(Request $request){
        // common saving code
        $link_id                  = $request->link_id;
        $username                 = $request->username;
        $user                     = User::where('username', $username)->where('is_deleted', 0)->first();
        if(!empty($link_id)){
            $link_model           = Link::find($link_id);
        }else{
            $link_model           = new Link();
            $link_model->user_id  = $user->id;
        }

        // save title if exist
        if($request->has('title'))
            $link_model->title    = $request->title;

        // save link if exist
        if($request->has('link_url'))
            $link_model->link_url = $request->link_url;

        // i assume 0- deactivate 1- activate
        if($request->has('active'))
            $link_model->active   = $request->active;

        // save file if exist
        if($request->hasFile('link_image')){
            $image                = $request->file('link_image');
            $file_name            = time() . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public', $file_name);
            $link_model->photo    = $file_name;
        }
        $link_model->save();
        $response_msg             = ['status' => 200, 'message' => 'Details Has been saved', 'link_id' => $link_model->_id, 'link_image' => $link_model->photo];
        /* if(empty($link_id)){
            $new_save             = [];
            $response_msg         = array_merge($response_msg, $new_save);
        } */
        return response(json_encode($response_msg));
    }

    public function linkUserLinks(Request $request){
        $username    = $request->username;
        $user        = User::where('username', $username)->where('is_deleted', 0)->first();
        if(!empty($user)){
            $allLinks = Link::where('user_id', $user->id)
            ->orderBy('sort_order')->get();
            return response(json_encode(['links' => $allLinks]));
        }else{
            return response(json_encode(['status' => 200, 'message' => 'user not found']));
        }
    }

    public function deleteLink(Request $request){
        $link_id     = $request->link_id;
        $username    = $request->username;
        $user        = User::where('username', $username)->where('is_deleted', 0)->first();
        if(!empty($user)){
            $delete_success = Link::where('_id', $link_id)->where('user_id', $user->id)->delete();
            return response(json_encode(['status' => 200, 'message' => (($delete_success)? 'link deleted successfully': 'something went wrong')]));
        }else{
            return response(json_encode(['status' => 200, 'message' => 'user not found']));
        }
    }

    public function SortUserLinks(Request $request){
        $link_ids = $request->link_ids;
        if(!empty($link_ids)){
            foreach ($link_ids as $sort_id => $link_id) {
                $link_sort = Link::find($link_id);
                $link_sort->sort_order = $sort_id;
                $link_sort->save();
            }
        }
        return response()->json(['status' => 200, 'message' => 'links are sorted successfully']);
    }

    public function showPublicLinks(Request $request){
        $username    = $request->username;
        $user        = User::where('username', $username)->where('is_deleted', 0)->first();
        if(!empty($user)){
            $allLinks = Link::where('user_id', $user->id)
            ->where('active', 1)
            ->orderBy('sort_order')->get();
            return response(json_encode(['links' => $allLinks]));
        }else{
            return response(json_encode(['status' => 200, 'message' => 'user not found']));
        }
    }
}
