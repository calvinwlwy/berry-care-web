<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleSsoAttemptLogger
{
    public function record(
        Request $request,
        string $outcome,
        mixed $email = null,
        ?string $reason = null,
        ?Throwable $exception = null
    ): void {
        $context = [
            'ip' => $request->ip(),
            'email' => $this->maskEmail($email),
            'outcome' => $outcome,
        ];

        if ($reason !== null) {
            $context['reason'] = $reason;
        }

        if ($exception !== null) {
            $context['exception'] = $exception::class;
            $context['detail'] = mb_substr($exception->getMessage(), 0, 500);
        }

        if (in_array($outcome, ['started', 'success'], true)) {
            Log::info('Google SSO attempt', $context);

            return;
        }

        Log::warning('Google SSO attempt', $context);
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
