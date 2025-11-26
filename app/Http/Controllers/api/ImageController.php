<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function avatar(Request $request){
        $avatar = $request->user()->image()->get(['path','alt']);
        return response()->json($avatar);
    }
}
