<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\ApplicationController;
use App\Http\Controllers\Mobile\ProgressController;
use App\Http\Controllers\Mobile\ProfileController;




Route::post('/login', [AuthController::class, 'apiStore']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'apiMobileLogout']);
    Route::post('/dashboard', [DashboardController::class, 'apiMobileDashboard']);


    Route::get('/application', [ApplicationController::class, 'api_application']);
    Route::post('/student/application/submit', [ApplicationController::class, 'submit']);
    Route::post('/student/application/{application}', [ApplicationController::class, 'update']);
    Route::delete('/student/application/{application}', [ApplicationController::class, 'delete']);


    Route::get('/student/progress', [ProgressController::class, 'progress']);

    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::patch('/update/profile', [ProfileController::class, 'update']);
    Route::put('/update/password', [ProfileController::class, 'updatePassword']);
    Route::delete('/delete/profile', [ProfileController::class, 'destroy']);
    

    //admin mobile side soon...


});








