<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PartnerController; // Added PartnerController
use App\Http\Controllers\ApplicationController; // Added ApplicationController
use App\Http\Controllers\ReportController; // Added ReportController
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware(['student'])->group(function () {
        Route::get('/student/dashboard', [StudentController::class, 'studentDashboard'])->name('student.dashboard');
        Route::get('/student/profile', [StudentController::class, 'studentProfile'])->name('student.profile');
        Route::get('/student/info', [StudentController::class, 'studentInfo'])->name('student.info');
        Route::get('/student/evaluation', [StudentController::class, 'studentEvaluation'])->name('student.evaluation');
        Route::get('/student/progress', [StudentController::class, 'studentProgress'])->name('student.progress');
        Route::get('/student/application', [StudentController::class, 'application'])->name('student.application');
        Route::post('/student/application', [StudentController::class, 'submitApplication'])->name('student.application.submit');
        Route::patch('/student/application/{application}', [StudentController::class, 'updateApplication'])->name('student.application.update');
        Route::delete('/student/application/{application}', [StudentController::class, 'deleteApplication'])->name('student.application.delete');
    });
});

// User Management Routes
Route::post('/users', [UserController::class, 'store'])->name('userr.store');
Route::patch('/users/{user}', [UserController::class, 'update'])->name('user.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('user.destroy');

Route::middleware(['admin'])->group(function () {
  

    Route::get('/admin/dashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/profile', [AdminController::class, 'adminProfile'])->name('admin.profile');

    Route::get('/admin/student', [AdminController::class, 'adminStudent'])->name('admin.student');
    Route::post('/saveUser', [AdminController::class, 'saveUser'])->name('user.store'); 
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('user.update'); 
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('user.destroy'); 

    Route::get('/admin/partner', [PartnerController::class, 'index'])->name('admin.partner');
    Route::get('/admin/program', [AdminController::class, 'adminProgram'])->name('admin.program');
    Route::post('/saveProgram', [AdminController::class, 'saveProgram'])->name('program.store'); 
    Route::patch('/programs/{program}', [AdminController::class, 'updateProgram'])->name('program.update'); 
    Route::delete('/programs/{program}', [AdminController::class, 'destroyProgram'])->name('program.destroy'); 

    Route::get('/admin/schedule', [AdminController::class, 'adminSchedule'])->name('admin.schedule');
    Route::get('/admin/evaluation', [AdminController::class, 'adminEvaluation'])->name('admin.evaluation');
    Route::get('/admin/application', [ApplicationController::class, 'index'])->name('admin.application');
    Route::get('/admin/application/{application}/resume', [ApplicationController::class, 'downloadResume'])->name('application.download.resume');
    Route::get('/admin/application/{application}/letter', [ApplicationController::class, 'downloadLetter'])->name('application.download.letter');
    Route::post('/admin/application', [ApplicationController::class, 'store'])->name('application.store');
    Route::patch('/admin/application/{application}', [ApplicationController::class, 'update'])->name('application.update');
    Route::delete('/admin/application/{application}', [ApplicationController::class, 'destroy'])->name('application.destroy');
    Route::get('/admin/report', [ReportController::class, 'index'])->name('admin.report');
  
    // Partner routes
    Route::post('/partners', [PartnerController::class, 'store'])->name('partner.store');
    Route::patch('/partners/{partner}', [PartnerController::class, 'update'])->name('partner.update');
    Route::delete('/partners/{partner}', [PartnerController::class, 'destroy'])->name('partner.destroy');

    // Report routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generateReport'])->name('reports.generate');
});
