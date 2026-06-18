<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! session()->has('employee')) {
            return redirect()->route('login');
        }

        $currentRole = (string) (session('employee')['role'] ?? '0');

        if ($role === 'manager' && $currentRole !== '1') {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}