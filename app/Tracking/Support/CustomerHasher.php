<?php

/**
 * SHA-256 normalization for Meta CAPI user_data fields.
 * Нормалізація та SHA-256 для полів user_data Meta CAPI.
 *
 * @package App\Tracking\Support
 */

declare(strict_types=1);

namespace App\Tracking\Support;

final class CustomerHasher
{
    /**
     * Hash email for Meta (lowercase, trimmed).
     * Хеш email для Meta (нижній регістр, trim).
     */
    public static function email(?string $email): ?string
    {
        if ($email === null || trim($email) === '') {
            return null;
        }

        return hash('sha256', strtolower(trim($email)));
    }

    /**
     * Hash phone for Meta (digits only).
     * Хеш телефону для Meta (лише цифри).
     */
    public static function phone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        return hash('sha256', $digits);
    }

    /**
     * Hash city or zip (lowercase, no spaces per Meta guidance).
     * Хеш міста або індексу (lowercase, без пробілів).
     */
    public static function locality(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = strtolower(preg_replace('/\s+/', '', trim($value)) ?? '');

        if ($normalized === '') {
            return null;
        }

        return hash('sha256', $normalized);
    }
}
