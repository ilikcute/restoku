<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Security headers yang ditambahkan ke setiap respons HTTP.
     * Mencegah XSS, Clickjacking, dan kebocoran informasi server.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah MIME-type sniffing oleh browser
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Mencegah clickjacking — halaman tidak boleh di-embed dalam iframe dari domain lain
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Paksa browser menggunakan HTTPS selama 1 tahun (aktifkan hanya jika sudah pakai SSL)
        // $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Kontrol informasi referrer yang dikirim saat navigasi
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Batasi fitur browser berbahaya (kamera, mikrofon, geolocation tanpa izin eksplisit)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Hapus header yang membocorkan informasi teknologi server
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Content Security Policy — permisif untuk SPA Vue.js + PrimeVue + CDN font
        // Sesuaikan 'script-src' dan 'style-src' jika ada pelanggaran di browser console
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ".
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ".
            "font-src 'self' https://fonts.gstatic.com data:; ".
            "img-src 'self' data: blob: https:; ".
            "connect-src 'self' ws: wss:; ".
            "frame-ancestors 'self';"
        );

        return $response;
    }
}
