<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function auth(Request $request){
        return response()->json(['message' => 'ok'],200);
    }
    public function show(Request $request){
        $user = $request->user();
        return response()->json($user);
    }
    public function update(Request $request){


    }
}
