<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and enforce robust enterprise security headers.
     * Prevents Clickjacking, MIME-type sniffing, XSS reflections, and restricts framing permissions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Legacy XSS protection filter
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Cross-Origin-Opener-Policy for OAuth popups
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        // Permissions Policy (control camera, microphone, geolocation access)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // HSTS enforcement for HTTPS/production
        if ($request->isSecure() || $request->header('x-forwarded-proto') === 'https' || app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy allowing YouTube embeds, HLS media streams, Cloudinary CDN, and local assets
        $csp = "default-src 'self' data: https:; ".
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://www.youtube.com https://s.ytimg.com https://*.youtube.com https://www.googletagmanager.com; ".
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; ".
               "font-src 'self' https://fonts.gstatic.com data:; ".
               "img-src 'self' data: https: blob: https://*.ytimg.com https://*.youtube.com https://res.cloudinary.com; ".
               "media-src 'self' https: blob: https://*.googlevideo.com; ".
               "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://*.youtube.com; ".
               "connect-src 'self' https: ws: wss: https://*.youtube.com https://*.googlevideo.com https://www.google-analytics.com;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
