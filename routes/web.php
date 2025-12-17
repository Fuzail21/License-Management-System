<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\LicenseRenewalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserLicenseController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\UserFeedbackController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes (Guest Middleware)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout Route (Auth Middleware)
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Admin Routes (Auth Middleware)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('departments', DepartmentController::class);
    Route::resource('users', UserController::class);

    Route::resource('vendors', VendorController::class);

    Route::resource('licenses', LicenseController::class);

    Route::resource('user-licenses', UserLicenseController::class);

    Route::get('renewals', [LicenseRenewalController::class, 'index'])->name('renewals.index');
    Route::get('renewals/create/{userLicense}', [LicenseRenewalController::class, 'create'])->name('renewals.create');
    Route::post('renewals/{userLicense}', [LicenseRenewalController::class, 'store'])->name('renewals.store');

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('reviews', [UserFeedbackController::class, 'index'])->name('reviews.index');

    Route::post('feedback/satisfied', [UserFeedbackController::class, 'satisfied'])
        ->name('feedback.satisfied');

    Route::post('feedback/issue', [UserFeedbackController::class, 'issue'])
        ->name('feedback.issue');
});
