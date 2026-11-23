<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;
use Modules\Auth\Http\Middleware\EnsureUserVerifiedMiddleware;
use Modules\UploadFile\Models\File;
use Modules\User\Http\Controllers\NotificationController;
use Modules\User\Http\Controllers\UserController;
use Modules\User\Http\Controllers\UserNotificationpreferenceController;

Route::get('/testsdfsdfsfdsf222', function (Request $request) {
    // $image = Image::decode($request->file('avatar'));
    // dd();
    // $png = $image->scale(height : 300)->encodeUsingFormat(Format::WEBP);
    //     Storage::disk('public')->put(time() . '.jpg', $png);

    // $image = Image::decode(Storage::disk('public')->get('sgB8zG.jpg'))
    //     ->scale(1000,800)->;

    // return response()->image($image, Format::WEBP, quality: 100);

});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('auth/me', [UserController::class, 'me'])->name('me')->middleware(EnsureUserVerifiedMiddleware::class);

    Route::prefix('profile')->group(function () {
        Route::get('notifications/preferences', [UserNotificationpreferenceController::class, 'index'])->name('notifications_preferences');
        Route::patch('notifications/preferences', [UserNotificationpreferenceController::class, 'update'])->name('notifications_preferences.update');
        Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications_unread');
        Route::get('notifications/read', [NotificationController::class, 'read'])->name('notifications_read');
    });
});
