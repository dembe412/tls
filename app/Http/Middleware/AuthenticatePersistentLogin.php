<?php

namespace App\Http\Middleware;

use App\Support\Security\PersistentSessions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePersistentLogin
{
    public function __construct(private PersistentSessions $persistent) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            $user = $this->persistent->authenticate($request);

            if ($user) {
                Auth::login($user, false);
            }
        }

        return $next($request);
    }
}
