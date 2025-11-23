<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as RulesPassword;

class AuthController extends Controller
{
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

        $user = User::firstOrCreate([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user,$validated['remember']);
        $request->session()->regenerate();
        return response()->json(['message' => 'ok'],200);
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'logout successfully!'],200);
    }
    public function delete(Request $request){
        $user = $request->user();
        $user->
        $user->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Account deleted successfully!'],200);
    }
}
