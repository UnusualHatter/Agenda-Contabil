<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Appointment;
use App\Support\DisplayTimezone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An appointment in the shape FullCalendar expects, with São Paulo wall
 * times and no offset.
 *
 * @mixin Appointment
 */
class AgendaEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->client->name,
            'start' => DisplayTimezone::toLocal($this->starts_at)->format('Y-m-d\TH:i:s'),
            'end' => DisplayTimezone::toLocal($this->ends_at)->format('Y-m-d\TH:i:s'),
            'url' => route('appointments.show', $this->resource),
            'classNames' => ['agenda-event', "agenda-event--{$this->status->value}"],
            'editable' => $this->status->canBeRescheduled() && $request->user()->can('update', $this->resource),
            'extendedProps' => [
                'service' => $this->service->name,
                'responsible' => $this->responsible->name,
                'status' => $this->status->label(),
                'pendingDocuments' => $this->pending_documents_count,
            ],
        ];
    }
}
