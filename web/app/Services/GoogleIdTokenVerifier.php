<?php

namespace App\Services;

use Google\Client;

class GoogleIdTokenVerifier
{
    private string $failureReason = 'invalid_google_token';

    /**
     * Verify the token signature and its configured audience using Google's public keys.
     *
     * @return array<string, mixed>|null
     */
    public function verify(string $idToken): ?array
    {
        $clientId = (string) config('services.google.client_id');
        if ($clientId === '') {
            $this->failureReason = 'google_client_id_missing';

            return null;
        }

        try {
            $client = new Client;
            $client->setClientId($clientId);
            $claims = $client->verifyIdToken($idToken);
        } catch (\Throwable $exception) {
            $this->failureReason = 'verification_error:'.$exception::class;

            throw $exception;
        }

        if (! is_array($claims)) {
            $this->failureReason = $this->hasMismatchedAudience($idToken, $clientId)
                ? 'google_client_id_mismatch'
                : 'google_signature_or_claims_invalid';

            return null;
        }

        $this->failureReason = 'verified';

        return $claims;
    }

    public function failureReason(): string
    {
        return $this->failureReason;
    }

    private function hasMismatchedAudience(string $idToken, string $clientId): bool
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            return false;
        }

        $payload = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($payload === false) {
            return false;
        }

        $claims = json_decode($payload, true);
        if (! is_array($claims) || ! is_string($claims['aud'] ?? null)) {
            return false;
        }

        return ! hash_equals($clientId, $claims['aud']);
    }
}
