<?php

declare(strict_types=1);

namespace App\Domain\Appointments\Actions;

use App\Models\Appointment;
use App\Models\User;
use App\Support\DisplayTimezone;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Call it inside a transaction: the row lock on the
 * responsible user makes concurrent bookings for the same person wait for
 * each other instead of both passing the overlap check.
 */
final class EnsureResponsibleIsAvailable
{
    public function handle(User $responsible, CarbonInterface $startsAt, CarbonInterface $endsAt, ?Appointment $ignore = null): void
    {
        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            throw ValidationException::withMessages([
                'ends_at' => __('appointments.errors.invalid_period'),
            ]);
        }

        if (! $responsible->canWrite()) {
            throw ValidationException::withMessages([
                'responsible_user_id' => __('appointments.errors.responsible_unavailable', ['name' => $responsible->name]),
            ]);
        }

        User::query()->whereKey($responsible->id)->lockForUpdate()->first();

        $conflict = Appointment::query()
            ->where('responsible_user_id', $responsible->id)
            ->blocking()
            ->overlapping($startsAt, $endsAt)
            ->when($ignore, fn (Builder $query) => $query->whereKeyNot($ignore->id))
            ->orderBy('starts_at')
            ->first();

        if ($conflict === null) {
            return;
        }

        throw ValidationException::withMessages([
            'starts_at' => __('appointments.errors.conflict', [
                'name' => $responsible->name,
                'start' => DisplayTimezone::toLocal($conflict->starts_at)->format('H:i'),
                'end' => DisplayTimezone::toLocal($conflict->ends_at)->format('H:i'),
            ]),
        ]);
    }
}
