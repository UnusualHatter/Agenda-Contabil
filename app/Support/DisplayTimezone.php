<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Carbon;

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

    public static function toUtc(string $value): Carbon
    {
        return Carbon::parse($value, self::zone())->utc();
    }
}
