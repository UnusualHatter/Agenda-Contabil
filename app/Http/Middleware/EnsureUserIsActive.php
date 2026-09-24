<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login already refuses deactivated accounts; this ends sessions that were
 * open when the account was deactivated.
 */
final class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null || $request->user()->active) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        abort_if($request->expectsJson(), Response::HTTP_UNAUTHORIZED);

        return redirect()->route('login')->withErrors(['email' => __('auth.inactive')]);
    }
}
