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

// Redirect the user to Google login
Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});

// User redirected here from Google after auth with them
Route::get('/auth/goog', function () {
    $user = Socialite::driver('google')->user();
    $googleToken = $user->token;

    // Check if the user in whitelist
    $authorizedEmail = explode(',', env('AUTH_EMAIL'));

    // Create JWT token for auth'd whitelisted user
    if (in_array($user->email, $authorizedEmail)) {
        $key = env('JWT_SECRET');
        $payload = array(
            "email" => $user->getEmail(),
            "firstName" => $user->user['given_name']
        );
        $token = JWT::encode($payload, $key, 'HS256');

        // Store the JWT token in a cookie
        $cookie = cookie('jwt_token', $token, 525600);

        // Store the Google oauth2 token in a cookie
        $googleTokenCookie = cookie('google_token', $googleToken, 525600);

        // Email is authorized, show site
        return redirect('/')->withCookie($cookie)->withCookie($googleTokenCookie);
    } else {
        // Email is not authorized, show error message
        return 'Unauthorized';
    }
});

// Log user out
Route::get('/logout', function () {

    // Get oauth2 access token
    $googleToken = Cookie::get('google_token');

    if ($googleToken !== null && $googleToken !== '') {

        // Revoke their google oauth2 token from google
        $googleClient = new Client();
        $googleClient->setAccessToken($googleToken);
        $googleClient->revokeToken();

        // Forget both the google oauth2 and JWT tokens and redirect to login
        $cookie = Cookie::forget('jwt_token');
        $googleToken = Cookie::forget('google_token');
        return redirect('/auth/redirect')->withCookie($cookie)->withCookie($googleToken);
    } else {
        return redirect('/auth/redirect');
    }
});