<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Socialite;

class SocialLoginController extends Controller
{
    public function redirect(Request $request){
        return Socialite::driver('github')->redirect();
    }
    public function callback(Request $request){
        $githubUser = Socialite::driver('github')->user();

        $githubId = $githubUser->getId();
        $githubToken = $githubUser->token;
        $githubRefreshToken = $githubUser->refreshToken;
        $githubEmail = $githubUser->getEmail();
        $githubName = $githubUser->getName();
        $githubAvatar = $githubUser->getAvatar();
        $githubUrl = $githubUser->user['html_url'] ?? '';

        $user = User::where('email',$githubEmail)->first();
        if($user){
            $user->socialAccounts()->updateOrCreate(
                ['provider_name' => 'github'],
                [
                    'nickname' => $githubName,
                    'provider_id' => $githubId,
                    'provider_token' => $githubToken,
                    'provider_refresh_token' => $githubRefreshToken,
                    'url' => $githubUrl,
                ]
            );
        }else{
            $userSocialAccount = SocialAccount::where('provider_name', 'github')->where('provider_id', $githubId)->first();
            if($userSocialAccount){
                $user = $userSocialAccount->user;
            }else{
                $user = User::create([
                    'email' => $githubEmail,
                    'name' => $githubName,
                ]);
                $user->socialAccounts()->create([
                    'nickname' => $githubName,
                    'provider_name' => 'github',
                    'provider_id' => $githubId,
                    'provider_token' => $githubToken,
                    'provider_refresh_token' => $githubRefreshToken,
                    'url' => $githubUrl,
                ]);
                event(new Registered($user));
            }

        }

        $user->image()->updateOrCreate([],[
            'path' => $githubAvatar,
            'alt' => $githubName
        ]);
        Auth::login($user, true);
        $request->session()->regenerate();
        return redirect(env('FRONT_URL'));
    }
}
