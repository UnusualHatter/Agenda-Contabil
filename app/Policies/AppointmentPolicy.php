<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->active;
    }

    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->canWrite();
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->canWrite();
    }

    // Soft delete is an administrative correction, never part of the daily
    // flow; cancelling is what the team uses. There is no forceDelete.
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->active && $user->isAdmin();
    }
}
