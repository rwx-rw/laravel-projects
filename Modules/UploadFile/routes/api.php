<?php

use Illuminate\Support\Facades\Route;
use Modules\UploadFile\Http\Controllers\UploadFileController;


Route::post('/upload-chunk',[UploadFileController::class,'upload']);

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('uploadfiles', UploadFileController::class)->names('uploadfile');
});
