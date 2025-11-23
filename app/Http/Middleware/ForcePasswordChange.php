<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->force_password_change) {
            if (!$request->is('change-password') && !$request->is('logout')) {
                return redirect()->route('change-password')
                    ->with('warning', 'You must change your password before continuing.');
            }
        }

        return $next($request);
    }
}

