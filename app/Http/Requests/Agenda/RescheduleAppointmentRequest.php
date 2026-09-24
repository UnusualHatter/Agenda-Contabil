<?php

declare(strict_types=1);

namespace App\Http\Requests\Agenda;

use App\Support\DisplayTimezone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('appointment'));
    }

    public function rules(): array
    {
        return [
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i:s'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i:s'],
        ];
    }

    public function startsAt(): Carbon
    {
        return DisplayTimezone::toUtc($this->string('starts_at')->toString());
    }

    public function endsAt(): Carbon
    {
        return DisplayTimezone::toUtc($this->string('ends_at')->toString());
    }
}
