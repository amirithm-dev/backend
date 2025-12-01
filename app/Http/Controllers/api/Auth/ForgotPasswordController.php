<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as RulesPassword;

class ForgotPasswordController extends Controller
{
    public function sendResetPasswordEmail(Request $request){
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email'),);

        return $status === Password::ResetLinkSent
        ?   response()->json(['status' => $status],200)
        :   response()->json(['status' => $status],400);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'token' => ['required'],
            'email' => ['required','email'],
            'password' => ['required','confirmed',RulesPassword::min(8)->max(30)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function (User $user,string $password){
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
        ? response()->json(['status' => $status],200)
        : response()->json(['status' => $status],400);
    }
}
