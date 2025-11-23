<?php

use App\Http\Controllers\GithubController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/oauth')->group(function(){
    Route::prefix('/github')->group(function(){
        Route::get('/redirect',[GithubController::class,'redirect']);
        Route::get('/callback',[GithubController::class,'callback']);
    });
});

