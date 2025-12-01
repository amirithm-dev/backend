<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Socialite;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

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
        $githubAvatarUrl = $githubUser->getAvatar();
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
        if($user->image && Storage::disk('public')->exists($user->image->path)){
            Storage::disk('public')->delete($user->image->path);
        }
        $githubAvatar = file_get_contents($githubAvatarUrl);
        $avatarName = Str::random(16) . '-' . time() . '.webp';
        $manager = new ImageManager(new Driver);
        $image = $manager->read($githubAvatar);
        $image->resize(300,300)
        ->toWebp();
        $encodedImage = $image->encode(new WebpEncoder(100));
        Storage::disk('public')->put("avatars/{$avatarName}",$encodedImage);
        $user->image()->updateOrCreate([],[
            'path' => "avatars/{$avatarName}",
            'alt' => $githubName
        ]);
        Auth::login($user, true);
        $request->session()->regenerate();
        return redirect(env('FRONT_URL'));
    }
}
