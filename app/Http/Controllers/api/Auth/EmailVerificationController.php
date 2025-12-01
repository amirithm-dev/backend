<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request){
        if($request->user()->hasVerifiedEmail()){
            return response()->json(['message' => 'Email already verified.'], 400);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent!']);
    }
    public function verifyEmail(Request $request){
        $user = User::findOrFail($request->id);

        if($user->hasVerifiedEmail()){
            return view('email_verified',[
                'message' => 'Email address already verified.',
                'verified' => true,
            ]);
        }
        if(! hash_equals($request->hash,sha1($user->getEmailForVerification()))){
            return view('email_verified',[
                'message' => 'Unable to verify email address.',
                'verified' => false,
            ]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
        return view('email_verified',[
            'message' => 'Email address has been verified.',
            'verified' => true,
        ]);
        
    }

}
