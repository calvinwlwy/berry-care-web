<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleIdTokenVerifier;
use App\Services\LoginAttemptLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function login(
        Request $request,
        GoogleIdTokenVerifier $verifier,
        LoginAttemptLogger $logger
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'id_token' => ['required', 'string', 'max:8192'],
        ], [
            'id_token.required' => 'ID token Google wajib diisi.',
            'id_token.string' => 'ID token Google harus berupa teks.',
            'id_token.max' => 'ID token Google terlalu panjang.',
        ]);

        if ($validator->fails()) {
            $logger->googleAttempt($request, null, 'invalid_input', false);

            return response()->json([
                'message' => 'Data yang diberikan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $verificationFailure = null;
        try {
            $claims = $verifier->verify($validator->validated()['id_token']);
        } catch (\Throwable $exception) {
            $verificationFailure = $verifier->failureReason();
            $claims = null;
        }

        $claimsFailure = $claims === null ? null : $this->claimsFailureReason($claims);
        if ($claims === null || $claimsFailure !== null) {
            $logger->googleAttempt(
                $request,
                $claims['email'] ?? null,
                $verificationFailure ?? ($claimsFailure ?? $verifier->failureReason()),
                false
            );

            return response()->json(['message' => 'Token Google tidak valid.'], 401);
        }

        $email = Str::lower($claims['email']);
        $googleId = $claims['sub'];
        $user = User::query()->where('google_id', $googleId)->first();

        if ($user === null) {
            $existingUser = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();
            if ($existingUser !== null) {
                $logger->googleAttempt($request, $email, 'email_already_registered', false);

                return response()->json([
                    'message' => 'Email ini sudah terdaftar. Masuk dengan password.',
                ], 409);
            }

            if (! config('services.google.auto_register')) {
                $logger->googleAttempt($request, $email, 'auto_registration_disabled', false);

                return response()->json([
                    'message' => 'Akun belum terdaftar. Hubungi admin.',
                ], 403);
            }

            try {
                $user = DB::transaction(function () use ($claims, $email, $googleId): User {
                    $existingUser = User::query()
                        ->whereRaw('LOWER(email) = ?', [$email])
                        ->lockForUpdate()
                        ->first();

                    if ($existingUser !== null) {
                        return $existingUser;
                    }

                    $name = $claims['name'] ?? Str::before($email, '@');

                    $user = User::create([
                        'name' => is_string($name) && trim($name) !== ''
                            ? mb_substr(trim($name), 0, 255)
                            : Str::before($email, '@'),
                        'email' => $email,
                        'password' => Str::random(64),
                        'role' => 'petani',
                        'google_id' => $googleId,
                    ]);
                    $user->forceFill(['email_verified_at' => now()])->save();

                    return $user;
                });
            } catch (QueryException $exception) {
                $user = User::query()->where('google_id', $googleId)->first();
                if ($user === null) {
                    $logger->googleAttempt($request, $email, 'account_creation_conflict', false);

                    if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                        return response()->json([
                            'message' => 'Email ini sudah terdaftar. Masuk dengan password.',
                        ], 409);
                    }

                    throw $exception;
                }
            }

            if ($user->google_id !== $googleId) {
                $logger->googleAttempt($request, $email, 'email_already_registered', false);

                return response()->json([
                    'message' => 'Email ini sudah terdaftar. Masuk dengan password.',
                ], 409);
            }
        }

        $logger->googleAttempt($request, $email, 'authenticated', true);

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private function claimsFailureReason(array $claims): ?string
    {
        $clientId = (string) config('services.google.client_id');
        $audience = $claims['aud'] ?? null;
        $issuer = $claims['iss'] ?? null;
        $email = $claims['email'] ?? null;
        $subject = $claims['sub'] ?? null;
        $expiresAt = $claims['exp'] ?? null;
        $emailVerified = $claims['email_verified'] ?? false;

        if ($clientId === '') {
            return 'google_client_id_missing';
        }
        if (! is_string($audience) || ! hash_equals($clientId, $audience)) {
            return 'google_client_id_mismatch';
        }
        if (! in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            return 'invalid_issuer';
        }
        if (! is_numeric($expiresAt) || (int) $expiresAt <= time()) {
            return 'expired_or_missing_expiry';
        }
        if (! ($emailVerified === true || $emailVerified === 'true')) {
            return 'email_not_verified';
        }
        if (
            ! is_string($email)
            || mb_strlen($email) > 255
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        ) {
            return 'invalid_email_claim';
        }
        if (! is_string($subject) || $subject === '' || mb_strlen($subject) > 255) {
            return 'invalid_subject_claim';
        }

        return null;
    }
}
