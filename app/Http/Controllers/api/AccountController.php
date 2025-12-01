<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as RulesPassword;

class AccountController extends Controller
{
    public function deleteAccount(Request $request){
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Account deleted successfully!'],200);
    }

    public function changePassword(Request $request){
        $validated = $request->validate([
            'currentPassword' => ['nullable',RulesPassword::min(8)->max(30)->letters()->numbers()],
            'password' => ['required',RulesPassword::min(8)->max(30)->letters()->numbers()],
        ]);
        $user = $request->user();
        if($user->password && !Hash::check($validated['currentPassword'],$user->password)){
            return response()->json(['message' => 'current password is incorrect.'],422);
        }
        $request->session()->regenerate();
        $request->session()->invalidate();
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);
        return response()->json(['message' => 'password has been changed.']);
    }
}
