<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as RulesPassword;

class RegisterController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'email' => 'email|required|max:255|unique:users,email',
            'password' => ['required','confirmed',RulesPassword::min(8)->max(30)->letters()->numbers()],
            'remember' => 'boolean|required'
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->image()->create([
            'path' => 'avatars/default-avatar.png',
            'alt' => 'avatar',
        ]);

        Auth::login($user,$validated['remember']);
        $request->session()->regenerate();
        event(new Registered($user));
        return response()->json(['message' => 'ok'],200);
    }
}
