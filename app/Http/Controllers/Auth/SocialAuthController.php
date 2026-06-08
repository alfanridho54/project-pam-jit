<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(AuditLogService $auditLog): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $created = false;
        $email = $googleUser->getEmail();

        abort_if(blank($email), 422, 'Google account did not provide an email address.');

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $created = true;
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $email,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(40)),
                'role' => 'user',
            ]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        $auditLog->log(
            $user,
            $created ? 'oauth_google_user_created' : 'oauth_google_login_succeeded',
            $user,
            $created ? 'Google OAuth user created and logged in.' : 'Google OAuth login succeeded.',
            [
                'provider' => 'google',
                'email' => $user->email,
                'created' => $created,
            ]
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
