<?php

declare(strict_types=1);

namespace App\Rules;

use App\Domain\Clients\Enums\ClientType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * CPF for individuals, CNPJ for organizations, both with check digits.
 */
final class BrazilianDocument implements ValidationRule
{
    public function __construct(private ClientType $type) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        $valid = match ($this->type) {
            ClientType::Individual => self::isCpf($digits),
            ClientType::Organization => self::isCnpj($digits),
        };

        if (! $valid) {
            $fail(__("clients.errors.invalid_{$this->type->value}_document"));
        }
    }

    public static function isCpf(string $digits): bool
    {
        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        return self::checkDigits($digits, 9, fn (int $position, int $length): int => $length + 1 - $position)
            && self::checkDigits($digits, 10, fn (int $position, int $length): int => $length + 1 - $position);
    }

    public static function isCnpj(string $digits): bool
    {
        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits)) {
            return false;
        }

        // Weights run 5..2 then 9..2 for the first digit, 6..2 then 9..2 for the second.
        $weight = fn (int $position, int $length): int => (($length - 1 - $position) % 8) + 2;

        return self::checkDigits($digits, 12, $weight) && self::checkDigits($digits, 13, $weight);
    }

    /**
     * @param  Closure(int, int): int  $weight  weight for the digit at a position among $length digits
     */
    private static function checkDigits(string $digits, int $length, Closure $weight): bool
    {
        $sum = 0;

        for ($position = 0; $position < $length; $position++) {
            $sum += (int) $digits[$position] * $weight($position, $length);
        }

        $expected = $sum % 11 < 2 ? 0 : 11 - $sum % 11;

        return (int) $digits[$length] === $expected;
    }
}
