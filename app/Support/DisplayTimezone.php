<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Carbon;

/**
 * Timestamps are persisted in UTC and presented in the project's local
 * timezone; conversion happens only here.
 */
final class DisplayTimezone
{
    public static function name(): string
    {
        return (string) config('app.display_timezone', 'America/Sao_Paulo');
    }

    public static function zone(): DateTimeZone
    {
        return new DateTimeZone(self::name());
    }

    public static function toLocal(DateTimeInterface $value): Carbon
    {
        return Carbon::instance($value)->setTimezone(self::zone());
    }

    /**
     * Interpret a value typed by a user (local time) as a UTC timestamp.
     */
    public static function toUtc(string $value): Carbon
    {
        return Carbon::parse($value, self::zone())->utc();
    }
}
