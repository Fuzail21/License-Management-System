<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $user = auth()->user();

        if (!$user->isManager() && !$user->isAdmin()) {
            // Log unauthorized access attempt
            AuditLogService::logUnauthorizedAccess(
                'manager_endpoint_access_denied',
                $user->id,
                [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_role' => $user->role?->name,
                ]
            );

            abort(403, 'This action requires manager or administrator privileges.');
        }

        return $next($request);
    }
}
