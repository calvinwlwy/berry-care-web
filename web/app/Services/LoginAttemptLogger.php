<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginAttemptLogger
{
    public function failed(Request $request, mixed $email, string $reason): void
    {
        Log::warning('Login attempt failed', [
            'ip' => $request->ip(),
            'email' => $this->maskEmail($email),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 200),
            'reason' => $reason,
        ]);
    }

    public function credentialsInQueryString(Request $request): void
    {
        $this->failed($request, $request->query('email'), 'credentials_in_query_string');
    }

    private function maskEmail(mixed $email): string
    {
        if (! is_string($email) || $email === '') {
            return '***';
        }

        $parts = explode('@', $email, 2);
        $local = $parts[0];
        $maskedLocal = mb_substr($local, 0, min(2, mb_strlen($local))).'***';

        return isset($parts[1]) ? $maskedLocal.'@'.$parts[1] : $maskedLocal;
    }
}
