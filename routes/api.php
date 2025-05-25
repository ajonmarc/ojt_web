<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Mobile\AuthController;

Route::post('/login', [AuthController::class, 'apiStore']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'apiMobileLogout']);

});








