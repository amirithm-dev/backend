<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as RulesPassword;

class LoginController extends Controller
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
    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'logout successfully!'],200);

    }

}
