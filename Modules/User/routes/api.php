<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Middleware\EnsureUserVerifiedMiddleware;
use Modules\UploadFile\Enums\DiskType;
use Modules\UploadFile\Services\FileUploadService;
use Modules\User\Http\Controllers\NotificationController;
use Modules\User\Http\Controllers\UserController;
use Modules\User\Http\Controllers\UserNotificationpreferenceController;
use Modules\User\Models\User;

Route::post('/testsdfsdfsfdsf222', function (Request $request) {
    // $file = FileUploadService::make(DiskType::PUBLIC, 'avatars')
    //     ->upload($request->file('avatar'), 'USER-COVER.jpeg', User::find(7), false);

    $file = upload_file($request->file('avatar'),['disk' => DiskType::LOCAL,'directory' => 'images']);
    return $file;
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
