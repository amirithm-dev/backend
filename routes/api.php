<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerifyController;
use App\Http\Middleware\GuestOnly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/',function(Request $request){
    dd($request->user());
});
Route::prefix('/auth')->group(function(){
    Route::post('/login',[AuthController::class,'login'])->middleware([GuestOnly::class,'throttle:5,1']);
    Route::post('/register',[AuthController::class,'register'])->middleware([GuestOnly::class,'throttle:5,1']);
    Route::post('/logout',[AuthController::class,'logout'])->middleware(['auth:sanctum','throttle:3,1']);
    Route::post('/deleteAccount',[AuthController::class,'delete'])->middleware(['auth:sanctum','throttle:deleteAccount']);
});


Route::get('/email/verify/{id}/{hash}', [EmailVerifyController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    if($request->user()->hasVerifiedEmail()){
        return response()->json(['message' => 'Email already verified.'], 400);
    }
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:sanctum', 'throttle:2,3'])->name('verification.send');
