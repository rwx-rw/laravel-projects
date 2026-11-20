<?php

use Illuminate\Support\Facades\Route;
use Modules\UploadFile\Http\Controllers\UploadFileController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('uploadfiles', UploadFileController::class)->names('uploadfile');
});
