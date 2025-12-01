<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebPushController extends Controller
{
    public function subscribe(Request $request){
        Auth::user()->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth'],
        );
        return response()->json(['message' => 'subscribed successfully.'],200);
    }
    public function updateSubscribe(Request $request){
    }
    public function removeSubscribe(Request $request){
        Auth::user()->deletePushSubscription();
        return response()->json(['message' => 'subscription deleted successfully.'],200);
    }

}
