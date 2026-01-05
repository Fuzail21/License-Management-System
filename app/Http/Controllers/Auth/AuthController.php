<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $key = 'login.' . $request->ip();

        // Check rate limiting (5 attempts per minute)
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->withInput($request->only('email'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Check if user is active
            if (Auth::user()->status !== UserStatus::Active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is inactive. Please contact administrator.',
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();
            RateLimiter::clear($key);

            return redirect()->intended(route('admin.dashboard'));
        }

        // Increment rate limiter
        RateLimiter::hit($key, 60);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Display the registration form.
     */
    public function showRegister(): View
    {
        $departments = \App\Models\Department::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('auth.register', compact('departments'));
    }

    /**
     * Handle registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        // Get default role (Staff)
        $defaultRole = \App\Models\Role::where('name', 'Staff')->first();

        // If Staff role doesn't exist, use first available role
        if (!$defaultRole) {
            $defaultRole = \App\Models\Role::first();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Auto-hashed by model cast
            'role_id' => $defaultRole?->id,
            'department_id' => $request->department_id,
            'phone' => $request->phone,
            'designation' => $request->designation,
            'status' => UserStatus::Active,
        ]);

        Auth::login($user);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Registration successful! Welcome to License Management System.');
    }

    /**
     * Handle logout request.
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
