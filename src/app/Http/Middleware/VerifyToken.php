<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VerifyToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('jwt_token');
        $key = env('JWT_SECRET');

        $authorizedEmail = explode(',', env('AUTH_EMAIL'));

        if (!$token) {
            return redirect('/auth/redirect');
        }

        try {
            $payload = JWT::decode($token, new Key($key, 'HS256'));
            $email = $payload->email;
        } catch (\Exception $e) {
            // dd($email . $e->getMessage());
            return redirect('/auth/redirect');
        }

        if (in_array($email, $authorizedEmail)) {
            return $next($request);
        } else {
            return redirect('/auth/redirect');
        }
    }
}