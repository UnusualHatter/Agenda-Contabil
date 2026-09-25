<?php

declare(strict_types=1);

namespace App\Domain\Appointments;

use App\Models\Appointment;
use App\Support\DisplayTimezone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

final class ReminderMessage
{
    public static function responseUrl(Appointment $appointment): string
    {
        return URL::temporarySignedRoute('appointments.respond', $appointment->starts_at, ['appointment' => $appointment]);
    }

    public static function subject(Appointment $appointment): string
    {
        return __('reminders.subject', ['date' => self::start($appointment)->translatedFormat('d/m')]);
    }

    /**
     * @return list<string>
     */
    public static function lines(Appointment $appointment): array
    {
        $start = self::start($appointment);
        $documents = $appointment->documents->pluck('name');

        return array_values(array_filter([
            __('reminders.greeting', ['name' => $appointment->client->greetingName()]),
            __('reminders.when', [
                'service' => $appointment->service->name,
                'weekday' => $appointment->weekday,
                'date' => $start->translatedFormat('j \d\e F'),
                'time' => $start->format('H:i'),
            ]),
            $appointment->location ? __('reminders.where', ['place' => $appointment->location]) : null,
            $documents->isEmpty() ? null : __('reminders.documents'),
            ...$documents->map(fn (string $name): string => "• {$name}"),
        ]));
    }

    public static function whatsAppUrl(Appointment $appointment): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $appointment->client->phone);

        if (strlen($digits) < 10) {
            return null;
        }

        $phone = str_starts_with($digits, '55') && strlen($digits) > 11 ? $digits : "55{$digits}";
        $text = implode("\n", [
            ...self::lines($appointment),
            '',
            __('reminders.respond_prompt').' '.self::responseUrl($appointment),
        ]);

        return "https://wa.me/{$phone}?text=".rawurlencode($text);
    }

    private static function start(Appointment $appointment): Carbon
    {
        return DisplayTimezone::toLocal($appointment->starts_at);
    }
}
