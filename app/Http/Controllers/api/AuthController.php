<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Auth\Events\Verified;

class AuthController extends Controller
{
    // login register logout delete-account
    public function login(Request $request){
        $validated = $request->validate([
            'email' => 'email|required',
            'password' => ['required',RulesPassword::min(8)->max(30)->letters()->numbers()],
            'remember' => 'boolean|required',
        ]);

        $result = Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ],$validated['remember']);


        if($result){
            $request->session()->regenerate();
            return response()->json(['message' => 'ok'],200);
        }

        return response()->json(['message' => 'unable to login with this credentials'],401);
    }
    public function register(Request $request){
        $validated = $request->validate([
            'email' => 'email|required|unique:users,email',
            'password' => ['required','confirmed',RulesPassword::min(8)->max(30)->letters()->numbers()],
            'remember' => 'boolean|required'
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user,$validated['remember']);
        $request->session()->regenerate();
        event(new Registered($user));
        return response()->json(['message' => 'ok'],200);
    }
    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'logout successfully!'],200);
    }
    public function deleteAccount(Request $request){
        $user = $request->user();
        $user->
        $user->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Account deleted successfully!'],200);
    }

    // email verification
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

    // password reset
}
