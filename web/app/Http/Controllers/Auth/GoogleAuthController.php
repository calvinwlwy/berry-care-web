<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleSsoAttemptLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    private const ACCESS_DENIED = 'Akun Google ini tidak memiliki akses.';

    public function redirect(Request $request, GoogleSsoAttemptLogger $logger): RedirectResponse
    {
        $logger->record($request, 'started');

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->enablePKCE()
            ->redirect();
    }

    public function callback(Request $request, GoogleSsoAttemptLogger $logger): RedirectResponse
    {
        if ($request->query('error') === 'access_denied') {
            $logger->record($request, 'rejected', reason: 'user_denied');

            return $this->loginError('Anda membatalkan proses masuk dengan Google.');
        }

        try {
            $googleUser = Socialite::driver('google')
                ->enablePKCE()
                ->user();
        } catch (InvalidStateException $exception) {
            $logger->record(
                $request,
                'rejected',
                reason: 'invalid_state',
                exception: $exception
            );

            return $this->loginError('Sesi masuk dengan Google tidak valid. Silakan coba lagi.');
        } catch (Throwable $exception) {
            $logger->record(
                $request,
                'rejected',
                reason: 'provider_error',
                exception: $exception
            );

            return $this->loginError('Login dengan Google gagal. Silakan coba lagi.');
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();
        $verified = $this->emailIsVerified($googleUser);

        if (! is_string($email) || $email === '' || ! is_string($googleId) || $googleId === '') {
            return $this->reject($request, $logger, $email, 'missing_identity');
        }

        if (! $verified) {
            return $this->reject($request, $logger, $email, 'email_not_verified');
        }

        $allowedDomain = strtolower(trim((string) config('services.google.allowed_domain')));
        $emailDomain = strtolower((string) strrchr($email, '@'));
        if ($allowedDomain !== '' && $emailDomain !== '@'.$allowedDomain) {
            return $this->reject($request, $logger, $email, 'domain_not_allowed');
        }

        try {
            $user = User::query()->where('google_id', $googleId)->first();
            if ($user === null) {
                $user = User::query()
                    ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])
                    ->first();
            }

            if (
                $user === null
                || $user->role !== 'admin'
                || ($user->google_id !== null && ! hash_equals((string) $user->google_id, $googleId))
            ) {
                return $this->reject($request, $logger, $email, 'account_not_allowed');
            }

            if ($user->google_id === null) {
                $user->google_id = $googleId;
                $user->save();
            }
        } catch (Throwable $exception) {
            $logger->record(
                $request,
                'rejected',
                $email,
                'account_link_failed',
                $exception
            );

            return $this->loginError(self::ACCESS_DENIED);
        }

        $request->session()->regenerate();
        Auth::login($user);

        $logger->record($request, 'success', $email);

        return redirect()->route('dashboard');
    }

    private function emailIsVerified(SocialiteUser $googleUser): bool
    {
        $rawUser = method_exists($googleUser, 'getRaw') ? $googleUser->getRaw() : [];

        return ($rawUser['email_verified'] ?? $rawUser['verified_email'] ?? false) === true;
    }

    private function reject(
        Request $request,
        GoogleSsoAttemptLogger $logger,
        mixed $email,
        string $reason
    ): RedirectResponse {
        $logger->record($request, 'rejected', $email, $reason);

        return $this->loginError(self::ACCESS_DENIED);
    }

    private function loginError(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}
