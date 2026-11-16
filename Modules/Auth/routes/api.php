<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Http\Controllers\VerificationController;
use Modules\Auth\Notifications\WelcomeMessage;
use Modules\User\Models\User;

Route::get('/test',function(){
    $user = User::find(7);
    $user->notify((new WelcomeMessage())->delay(now()->addSecond(10)));
});

Route::middleware([])->prefix('v1/auth')->group(function () {
    Route::post('check-user', [AuthController::class, 'checkUser'])->name('check-user')->middleware('throttle:check-user');

    Route::post('code-verification/send', [VerificationController::class,'sendCode'])->name('code-verification-code')->middleware('throttle:verification-code');
    
    Route::post('code-verification/verify', [VerificationController::class,'verifyCode'])->name('code-verification-verify')->middleware('throttle:verification-code');

    Route::post('register', [AuthController::class, 'register'])->name('register')->middleware('throttle:auth_user');

    Route::post('login', [AuthController::class, 'login'])->name('login')->middleware('throttle:auth_user');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password')->middleware('throttle:auth_user');
});
