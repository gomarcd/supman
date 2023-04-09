<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Firebase\JWT\JWT;
use App\Http\Middleware\VerifyToken;
use Illuminate\Support\Facades\Http;
use Google\Client;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Apply the oauth2 Middleware only in production
if (App::environment('production')) {
    Route::get('/', function () {
        return view('welcome');
    })->middleware(VerifyToken::class);
} else {
    Route::get('/', function () {
        return view('welcome');
    });
}

// Log in with Google oauth2
Route::get('/auth/redirect', function () {
    return Socialite::driver('google')
        ->with(['access_type' => 'offline', 'prompt' => 'consent'])
        ->redirect();
});

// User redirected here from Google after auth with them
Route::get('/auth/goog', function () {

    // Get whitelisted users
    $whitelist = explode(',', env('AUTH_EMAIL'));

    // Get logged in user
    $user = Socialite::driver('google')->user();

    // Get Google Access Token
    $googleToken = $user->token;
    $googleRefresh = $user->refreshToken;

    // Give whitelisted users JWT token
    if (in_array($user->email, $whitelist)) {
        $key = env('JWT_SECRET');
        $payload = array(
            "email" => $user->getEmail(),
            "firstName" => $user->user['given_name']
        );
        $jwtToken = JWT::encode($payload, $key, 'HS256');

        // Store tokens
        $jwtTokenCookie = cookie('jwt_token', $jwtToken, 525600);
        $googleTokenCookie = cookie('google_token', $googleToken, $user->expiresIn);
        $googleRefreshCookie = cookie('google_refresh', $googleRefresh, 525600);

        // Show the site
        return redirect('/')->withCookie($jwtTokenCookie)->withCookie($googleTokenCookie)->withCookie($googleRefreshCookie);

    } else {
        return 'Denied.';
    }
});

// Log user out
Route::get('/logout', function () {

    // Revoke access token with oauth2 provider
    if (Cookie::get('google_token')) {
        $accessToken = Cookie::get('google_token');
        $client = new Client();
        $client->setClientId(env('G_CID'));
        $client->setClientSecret(env('G_SEC'));
        $client->revokeToken($accessToken);

        // Expire corresponding cookie
        (Cookie::expire('google_token'));

        // Expire cookie with JWT token
        if (Cookie::get('jwt_token')) {
            (Cookie::expire('jwt_token'));

            // Send back to oauth2
            return redirect('/auth/redirect');
        }

        // Send back to oauth2
        return redirect('/auth/redirect');

    } else {

        // Expire cookie with JWT token
        if (Cookie::get('jwt_token')) {
            (Cookie::expire('jwt_token'));

            // Send back to oauth2
            return redirect('/auth/redirect');
        }

        // Send back to oauth2
        return redirect('/auth/redirect');
    }

});