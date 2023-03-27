<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Firebase\JWT\JWT;
use App\Http\Middleware\VerifyToken;

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

Route::get('/', function () {
    return view('welcome');
})->middleware(VerifyToken::class);

// Redirect the user to Google login
Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});

// User redirected here from Google after auth with them
Route::get('/auth/goog', function () {
    $user = Socialite::driver('google')->user();

    // Check if the user in whitelist
    $authorizedEmail = explode(',', env('AUTH_EMAIL'));

    // Create JWT token for auth'd whitelisted user
    if (in_array($user->email, $authorizedEmail)) {
        $key = env('JWT_SECRET');
        $payload = array(
            "email" => $user->getEmail(),
        );

        $token = JWT::encode($payload, $key, 'HS256');

        // Store the JWT token in a cookie
        $cookie = cookie('jwt_token', $token, 525600);

        // Email is authorized, show site
        return redirect('/')->withCookie($cookie);
    } else {
        // Email is not authorized, show error message
        return 'Unauthorized';
    }

});