<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Firebase\JWT\JWT;

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
});

// Redirect the user to Google login
Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});
 
// User redirected here from Google after auth with them
Route::get('/auth/goog', function () {
    $user = Socialite::driver('google')->user();

    // Check if the user's email is authorized
    $authorizedEmail = [env('AUTH_EMAIL')];

    // If so create JWT token
    if (in_array($user->email, $authorizedEmail)) {
        $key = env('JWT_SECRET');
        $payload = array(
            "email" => $user->getEmail(),
        );
        $token = JWT::encode($payload, $key);

        dd($token);
        // Email is authorized, show site
        return view('welcome');
    } else {
        // Email is not authorized, show error message
        return 'Unauthorized';
    }

});

//     // Check if user's email address is allowed to register
//     $allowedEmails = ['user1@example.com', 'user2@example.com'];
//     if (!in_array($user->email, $allowedEmails)) {
//         return redirect('/')->withErrors(['You are not allowed to register.']);
 
//     // $user->token
// });