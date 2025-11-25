<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerifyController;
use App\Http\Middleware\GuestOnly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/',function(Request $request){
    dd($request->user());
});

Route::prefix('/auth')->group(function(){
    Route::post('/login',[AuthController::class,'login'])->middleware([GuestOnly::class,'throttle:5,1']);
    Route::post('/register',[AuthController::class,'register'])->middleware([GuestOnly::class,'throttle:5,1']);
    Route::post('/logout',[AuthController::class,'logout'])->middleware(['auth:sanctum','throttle:3,1']);
    Route::post('/deleteAccount',[AuthController::class,'deleteAccount'])->middleware(['auth:sanctum','throttle:deleteAccount']);
    
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed','throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class,'sendVerificationEmail'])->middleware(['auth:sanctum', 'throttle:2,3'])->name('verification.send');
});
