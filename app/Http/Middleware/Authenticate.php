<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // For API routes, return null to send a JSON response
        if ($request->is('api/*')) {
            return null;
        }

        // For web routes, redirect to login
        return route('login');
    }
}
