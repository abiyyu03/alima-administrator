<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Allowed routes for tutor role (route name prefixes).
     */
    private const TUTOR_ALLOWED = [
        'my-presences',
        'dashboard',
        'settings',
        'logout',
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $userRole = $user->role?->name;

        // Superadmin can access everything
        if ($userRole === 'superadmin') {
            return $next($request);
        }

        // If specific roles required, check membership
        if (! empty($roles) && ! in_array($userRole, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        // Tutor can only access allowed routes
        if ($userRole === 'tutor') {
            $routeName = $request->route()?->getName() ?? '';

            $allowed = collect(self::TUTOR_ALLOWED)
                ->some(fn($prefix) => str_starts_with($routeName, $prefix));

            if (! $allowed) {
                abort(403, 'Tutor hanya dapat mengakses halaman presensi.');
            }
        }

        return $next($request);
    }
}
