<?php

namespace App\Http\Middleware;

use Closure;

class CheckSchoolSession
{
    public function handle($request, Closure $next)
    {
        if (
            auth()->check()
            &&
            !session()->has('yearid')
            &&
            !session()->has('sectionid')
            &&
            !$request->is('selection*')
        ) {

            auth()->logout();

            session()->invalidate();

            session()->regenerateToken();

            return redirect('/login')
                ->with(
                    'error',
                    'Session expired. Please login again.'
                );
        }

        return $next($request);
    }
}