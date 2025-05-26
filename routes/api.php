<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\ApplicationController;
use App\Http\Controllers\Mobile\ProgressController;
use App\Http\Controllers\Mobile\ProfileController;
use App\Http\Controllers\Mobile\Admin\AdminController;





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

    Route::get('/admin/home', [AdminController::class, 'admin_home']);
    Route::get('/admin/students', [AdminController::class, 'students']);
    Route::post('/admin/students', [AdminController::class, 'storeStudent']);
    Route::put('/admin/students/{id}', [AdminController::class, 'updateStudent']);
    Route::delete('/admin/students/{id}', [AdminController::class, 'deleteStudent']);

    Route::get('/admin/programs', [AdminController::class, 'programs']);
    Route::post('/admin/programs', [AdminController::class, 'storeProgram']);
    Route::put('/admin/programs/{id}', [AdminController::class, 'updateProgram']);
    Route::delete('/admin/programs/{id}', [AdminController::class, 'deleteProgram']);


    Route::get('/admin/partners', [AdminController::class, 'partners']);
    Route::post('/admin/partners', [AdminController::class, 'storePartner']);
    Route::put('/admin/partners/{id}', [AdminController::class, 'updatePartner']);
    Route::delete('/admin/partners/{id}', [AdminController::class, 'deletePartner']);

    Route::get('/admin/applications', [AdminController::class, 'applications']);
    Route::post('/admin/applications', [AdminController::class, 'storeApplication']);
    Route::put('/admin/applications/{id}', [AdminController::class, 'updateApplication']);
    Route::delete('/admin/applications/{id}', [AdminController::class, 'deleteApplication']);
    Route::get('/admin/applications/{id}/resume', [AdminController::class, 'downloadResume']);
    Route::get('/admin/applications/{id}/letter', [AdminController::class, 'downloadLetter']);

    Route::get('/admin/report', [AdminController::class, 'report']);


});








