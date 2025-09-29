<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicLoginController extends Controller
{
    /**
     * Handle the magic login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request, $token)
    {
        $user = User::where('remember_token', $token)->first();

        if ($user) {
            // Log the user in
            Auth::login($user);

            // Clear the remember token so it can't be used again
            $user->remember_token = null;
            $user->save();

            // Redirect to the frontend plans page with a minimal layout
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:8080');
            return redirect($frontendUrl . '/subscriptions/plans?layout=minimal');
        }

        // If the token is invalid, redirect to the login page
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:8080');
        return redirect($frontendUrl . '/login');
    }
}
