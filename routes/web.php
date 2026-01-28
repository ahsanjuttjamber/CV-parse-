<?php

use App\Http\Controllers\CvController;
use App\Http\Controllers\CvApiController;

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ----------------- Guest Routes -----------------
Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register.form');
    Route::post('register', [AuthController::class, 'register'])->name('register');

    Route::get('otp', [AuthController::class, 'showOtpForm'])->name('otp.form');
    Route::post('otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');

    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

// --------------- Authenticated Routes ----------------
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // User Dashboard
    Route::get('/user/dashboard', function () {
        return view('dashboards.user');
    })->name('user.dashboard')->middleware('role:user');

    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.admin');
    })->name('admin.dashboard')->middleware('role:admin');
});

// ---------------- Admin CV Management ----------------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
  });








Route::get('/cv/{filename}', [CvApiController::class, 'autoParseCv'])
    ->where('filename', '.*');

Route::get('/cv/review/{filename}', [CvApiController::class, 'reviewCv'])
    ->where('filename', '.*');
