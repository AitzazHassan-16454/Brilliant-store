<?php

namespace App\Http\Middleware;

use App\Support\ApplicationPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            Auth::check() &&
            Auth::user()->hasAnyPermission(ApplicationPermissions::all())
        ) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
