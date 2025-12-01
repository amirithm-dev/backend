<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\WebPush\TestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class UserController extends Controller
{
    public function show(Request $request){
        $user = $request->user();
        return response()->json($user);
    }

    public function avatar(Request $request){
        $avatarModel = $request->user()->image;
        return response()->json([
            'path' => asset(Storage::url($avatarModel->path)),
            'alt' => $avatarModel->alt,
        ]);
    }

    public function update(Request $request){
        $validated = $request->validate([
            'avatar' => 'nullable|image|max:2048',
            'name' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'number'=> 'nullable|min_digits:11|max_digits:11|numeric',
        ]);
        $credentials = array_filter($validated,fn($value) => !is_null($value));
        $user = $request->user();

        // save avatar
        if($request->hasFile('avatar')){
            $imageModel = $user->image;
            if(Storage::disk('public')->exists($imageModel->path)){
                if(ltrim($imageModel->path, '/') !== ("avatars/" . env('DEFAULT_USER_AVATAR','default-avatar.png'))){
                    Storage::disk('public')->delete($imageModel->path);
                }
            }
            $avatar = $request->file('avatar');
            $avatarName = Str::random(16) . '-' . pathinfo($avatar->getClientOriginalName(),PATHINFO_FILENAME) . '-' . time() . '.webp';

            $manager = new ImageManager(new Driver);
            $image = $manager->read($avatar->getPathname());
            $image->resize(300,300)->toWebp();
            $encodedImage = $image->encode(new WebpEncoder(60));
            Storage::disk('public')->put("avatars/{$avatarName}",$encodedImage);

            $imageModel->path = "avatars/{$avatarName}";
            $imageModel->alt = $avatarName;
            $imageModel->save();
        }
        // update information
        $user->update($credentials);

        return response()->json(['message' => 'ok'],200);
    }

}
