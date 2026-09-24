<?php

declare(strict_types=1);

namespace App\Domain\Appointments\Actions;

use App\Domain\Appointments\Enums\ActivityType;
use App\Domain\Appointments\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Covers confirm, start, complete, cancel and no-show. No transition goes
 * from a free status back to a blocking one, so no conflict check is needed.
 */
final class ChangeAppointmentStatus
{
    public function handle(User $author, Appointment $appointment, AppointmentStatus $next): Appointment
    {
        $current = $appointment->status;

        if (! $current->canTransitionTo($next)) {
            throw ValidationException::withMessages([
                'status' => __('appointments.errors.transition_not_allowed', [
                    'from' => $current->label(),
                    'to' => $next->label(),
                ]),
            ]);
        }

        return DB::transaction(function () use ($author, $appointment, $current, $next): Appointment {
            $appointment->update([
                'status' => $next,
                'updated_by' => $author->id,
            ]);

            $appointment->recordActivity(
                $next === AppointmentStatus::Cancelled ? ActivityType::Cancelled : ActivityType::StatusChanged,
                $author,
                ['status' => $current->value],
                ['status' => $next->value],
            );

            return $appointment;
        });
    }
}
