<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminController;

Route::post('/login', [AuthenticatedSessionController::class, 'apiStore']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'apiMobileLogout']);
   Route::get('/admin/students', [AdminController::class, 'apiAdminStudent']);
    Route::post('/admin/students', [AdminController::class, 'apiSaveUser']);
    Route::patch('/admin/students/{user}', [AdminController::class, 'apiUpdateUser']);
    Route::delete('/admin/students/{user}', [AdminController::class, 'apiDestroyUser']);
});








