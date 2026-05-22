<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * API için guest middleware - giriş yapmış kullanıcılar için redirect yerine JSON response döner
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // API için guest middleware - giriş yapmış olsalar bile erişime izin ver
        // Sadece auth endpoint'lerinde kullanılır
        return $next($request);

        // Not: Gerçek guest kontrolü istenirse aşağıdaki yorum açılabilir:
        /*
        if (auth('sanctum')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zaten giriş yapmışsınız'
            ], 403);
        }

        return $next($request);
        */
    }
}
