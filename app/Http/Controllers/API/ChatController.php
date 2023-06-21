<?php

namespace App\Http\Controllers\API;


use Request;
use PRedis;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatController extends Controller
{
     public function __construct()
    {
        // $this->middleware('guest');
    }


    public function sendMessage(Request $request)
    {
        $redis = PRedis::connection();
        
        $data = ['message' => Request::input('message'), 'user' => Request::input('user')];
        
        $redis->publish('message', json_encode($data));
        
        return response()->json(['success' => true]);
    }
}
