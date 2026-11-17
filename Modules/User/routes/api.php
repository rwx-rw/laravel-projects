<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Middleware\EnsureUserVerifiedMiddleware;
use Modules\User\Http\Controllers\UserController;
use Modules\Auth\Notifications\WelcomeMessage;
use Modules\TelegramBot\Notifications\Channels\TelegramChannel;
use Modules\User\Http\Controllers\UserNotificationpreferenceController;
use Modules\User\Models\User;
use Modules\User\Services\NotificationPereferenceService;

Route::get('/test',function(){
    $user = User::find(7);
    $user->notify((new WelcomeMessage()));

    // $service = new NotificationPereferenceService;
    // $result = $service->update(User::find(7),'welcome_message','telegram',true);
    // $result = $service->allowedChannels($user,'welcome_message');
    // dd($result);
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('auth/me',[UserController::class,'me'])->name('me')->middleware(EnsureUserVerifiedMiddleware::class);

    Route::prefix('profile')->group(function(){
        Route::get('notifications/preferences',[UserNotificationpreferenceController::class,'index'])->name('notifications_preferences');
        Route::patch('notifications/preferences',[UserNotificationpreferenceController::class,'update'])->name('notifications_preferences.update');
    });
});
