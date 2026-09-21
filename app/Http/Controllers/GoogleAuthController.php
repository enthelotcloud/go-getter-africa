<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->with('status', 'Google sign-in failed. Please try again.');
        }

        $user = User::where('google_id', $googleUser->id)->first()
            ?? User::where('email', $googleUser->email)->first();

        if ($user) {
            // Link google_id if this is an existing email/password account
            if (empty($user->google_id)) {
                $user->forceFill(['google_id' => $googleUser->id])->save();
            }
        } else {
            $user = User::create([
                'name'              => $googleUser->name,
                'email'             => $googleUser->email,
                'google_id'         => $googleUser->id,
                'password'          => null, // Google-only account
                'email_verified_at' => now(),
                'role'              => 'voter',
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended('/');
    }
}
