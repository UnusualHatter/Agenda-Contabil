<?php

declare(strict_types=1);

namespace App\Http\Requests\Agenda;

use App\Support\DisplayTimezone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/**
 * FullCalendar runs with timeZone "UTC" and receives São Paulo wall times,
 * so the "Z" it sends back is not a real UTC offset: the date and time are
 * local and are converted to UTC here.
 */
class AgendaEventsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'responsible' => ['nullable', 'integer', 'exists:users,id'],
            'cancelled' => ['nullable', 'boolean'],
        ];
    }

    public function from(): Carbon
    {
        return self::wallTimeToUtc($this->string('start')->toString());
    }

    public function to(): Carbon
    {
        return self::wallTimeToUtc($this->string('end')->toString());
    }

    public function responsibleId(): ?int
    {
        return $this->integer('responsible') ?: null;
    }

    private static function wallTimeToUtc(string $value): Carbon
    {
        return DisplayTimezone::toUtc(Carbon::parse($value)->format('Y-m-d H:i:s'));
    }
}
