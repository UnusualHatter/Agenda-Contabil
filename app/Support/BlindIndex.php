<?php

declare(strict_types=1);

namespace App\Support;

// HMAC of an encrypted value, for exact lookups without storing it in clear.
final class BlindIndex
{
    public static function forDocument(?string $document): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $document);

        if ($digits === '') {
            return null;
        }

        return hash_hmac('sha256', 'document:'.$digits, (string) config('app.key'));
    }
}
