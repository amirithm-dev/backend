<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TowFAController extends Controller
{
    public function enable2fa(Request $request){
        $google2fa = new Google2FA();
        $secret =$google2fa->generateSecretKey();
        $qr = $google2fa->getQRCodeUrl(
            env('APP_NAME'),
            $request->user()->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr' => $qr,
        ],200);
    }
    public function confirm2fa(Request $request){
        $validated = $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string',
        ]);
        $google2fa = new Google2FA();
        $validate = $google2fa->verifyKey($validated['secret'],$validated['code']);
        if(!$validate){
            return response()->json(['message' => 'Invalid verification code.'],422);
        }
        $user = $request->user();
        $user->two_factor_secret = $validated['secret'];
        $user->two_factor_confirmed_at = now();
        $user->save();
        return response()->json(['message' => 'Two-factor authentication enabled successfully.'],200);

    }
    public function verify2fa(Request $request){
        $validate = $request->validate([
            'code' => 'required|string',
        ]);
        $user = $request->user();
        if(!$user->two_factor_secret){
            return response()->json(['message' => 'Two-factor authentication is not enabled.'],422);
        }
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret,$validate['code']);
        if(!$valid){
            return response()->json(['message' => 'Invalid verification code.'],422);
        }
        session('2fa_verified' , Carbon::now());
        return response()->json(['message' => 'Two-factor authentication verified successfully.'],200);
    }
    public function disable2fa(Request $request){
        $user = $request->user();
        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->save();
        return response()->json(['message' => 'Two-factor authentication disabled successfully.'],200);
    }
}
