<?php

use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\LicenseRenewalController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
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

/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Authentication Routes (Guest)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Logout Route (Authenticated)
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Common Routes (Admin, Manager, Staff)
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        /*
        |--------------------------------------------------------------------------
        | Manager + Admin Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:Admin,Manager', 'throttle:60,1'])->group(function () {

            // Departments
            Route::resource('departments', DepartmentController::class);
            Route::post(
                'departments/{department}/toggle-status',
                [DepartmentController::class, 'toggleStatus']
            )->name('departments.toggle-status');

            // Divisions
            Route::resource('divisions', DivisionController::class);

            // Employees
            Route::resource('employees', EmployeeController::class);
            Route::post(
                'employees/bulk-delete',
                [EmployeeController::class, 'bulkDelete']
            )->name('employees.bulk-delete');

            Route::get(
                'employees/export',
                [EmployeeController::class, 'export']
            )->name('employees.export');

            // Vendors
            Route::resource('vendors', VendorController::class);

            // Licenses
            Route::resource('licenses', LicenseController::class);
            Route::resource('user-licenses', UserLicenseController::class);

            // Renewals
            Route::get('renewals', [LicenseRenewalController::class, 'index'])
                ->name('renewals.index');

            Route::get(
                'renewals/create/{license}',
                [LicenseRenewalController::class, 'create']
            )->name('renewals.create');

            Route::post(
                'renewals/{license}',
                [LicenseRenewalController::class, 'store']
            )->name('renewals.store');
        });

        /*
        |--------------------------------------------------------------------------
        | Admin-Only Routes
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:Admin')->group(function () {

            // Settings
            Route::get('settings', [SettingsController::class, 'index'])
                ->name('settings.index');

            Route::put('settings', [SettingsController::class, 'update'])
                ->name('settings.update');

            // User & Role Management
            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);

            // Cities
            Route::resource('cities', CityController::class);

            Route::post(
                'cities/{city}/force-delete',
                [CityController::class, 'forceDelete']
            )->name('cities.force-delete');

            Route::post(
                'cities/{city}/archive',
                [CityController::class, 'archive']
            )->name('cities.archive');

            Route::post(
                'cities/{city}/restore',
                [CityController::class, 'restore']
            )->name('cities.restore');

            // City Manager Assignments
            Route::get(
                'cities/{city}/managers',
                [CityController::class, 'managers']
            )->name('cities.managers');

            Route::post(
                'cities/{city}/managers',
                [CityController::class, 'assignManager']
            )->name('cities.assign-manager');

            Route::delete(
                'cities/{city}/managers/{manager}',
                [CityController::class, 'removeManager']
            )->name('cities.remove-manager');
        });

    // Route::get('reviews', [UserFeedbackController::class, 'index'])->name('reviews.index');

    // Route::post('feedback/satisfied', [UserFeedbackController::class, 'satisfied'])
    //     ->name('feedback.satisfied');

    // Route::post('feedback/issue', [UserFeedbackController::class, 'issue'])
    //     ->name('feedback.issue');
});
