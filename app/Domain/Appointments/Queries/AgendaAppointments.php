<?php

declare(strict_types=1);

namespace App\Domain\Appointments\Queries;

use App\Domain\Appointments\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class AgendaAppointments
{
    /**
     * Cancelled and no-show appointments are hidden unless asked for: they
     * free the slot, and showing them by default makes the week look full.
     *
     * @return Collection<int, Appointment>
     */
    public static function between(
        CarbonInterface $from,
        CarbonInterface $to,
        ?int $responsibleId = null,
        bool $includeCancelled = false,
    ): Collection {
        return Appointment::query()
            ->with(['client', 'service', 'responsible'])
            ->withPendingDocumentsCount()
            ->overlapping($from, $to)
            ->when($responsibleId, fn (Builder $query) => $query->where('responsible_user_id', $responsibleId))
            ->unless($includeCancelled, fn (Builder $query) => $query->whereNotIn('status', [
                AppointmentStatus::Cancelled,
                AppointmentStatus::NoShow,
            ]))
            ->orderBy('starts_at')
            ->get();
    }
}
