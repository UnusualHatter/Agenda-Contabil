<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        $response->headers->add([
            'Content-Security-Policy' => $this->contentSecurityPolicy(),
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'same-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // "Back" after logging out on a shared computer must not show personal data.
        if ($request->user() !== null) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $dev = $this->viteDevServer();

        return implode('; ', [
            "default-src 'self'",
            // Alpine evaluates x-* expressions with new Function.
            "script-src 'self' 'nonce-".Vite::cspNonce()."' 'unsafe-eval' {$dev}",
            "style-src 'self' 'unsafe-inline' {$dev}",
            "img-src 'self' data:",
            // FullCalendar's icon font is inlined as data:.
            "font-src 'self' data: {$dev}",
            "connect-src 'self' {$dev}".($dev === '' ? '' : ' '.str_replace('http', 'ws', $dev)),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }

    private function viteDevServer(): string
    {
        if (! Vite::isRunningHot()) {
            return '';
        }

        return trim((string) file_get_contents(Vite::hotFile()));
    }
}
