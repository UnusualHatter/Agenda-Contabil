<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Appointments\Actions\SendAppointmentReminder;
use App\Domain\Appointments\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

final class SendDueReminders extends Command
{
    private const HOURS_BEFORE = 24;

    protected $signature = 'appointments:send-reminders';

    protected $description = 'E-mail a reminder to clients whose appointment starts within the next day';

    public function handle(SendAppointmentReminder $send): int
    {
        $sent = Appointment::query()
            ->with(['client', 'service', 'documents'])
            ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [now(), now()->addHours(self::HOURS_BEFORE)])
            ->whereHas('client', fn (Builder $query) => $query
                ->where('active', true)
                ->where('accepts_reminders', true)
                ->whereNotNull('email'))
            ->get()
            ->filter(fn (Appointment $appointment): bool => $send->handle($appointment))
            ->count();

        $this->components->info(trans_choice('reminders.command_result', $sent, ['count' => $sent]));

        return self::SUCCESS;
    }
}
