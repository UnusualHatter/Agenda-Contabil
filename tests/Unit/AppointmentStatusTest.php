<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Appointments\Enums\AppointmentStatus;
use PHPUnit\Framework\TestCase;

class AppointmentStatusTest extends TestCase
{
    public function test_finished_statuses_are_terminal(): void
    {
        foreach ([AppointmentStatus::Completed, AppointmentStatus::Cancelled, AppointmentStatus::NoShow] as $status) {
            $this->assertSame([], $status->allowedTransitions(), $status->value);
        }
    }

    // ChangeAppointmentStatus skips the overlap check because of this.
    public function test_no_transition_makes_a_free_period_busy_again(): void
    {
        $reachableFromFree = collect(AppointmentStatus::cases())
            ->reject(fn (AppointmentStatus $status): bool => in_array($status, AppointmentStatus::blocking(), true))
            ->flatMap(fn (AppointmentStatus $status): array => $status->allowedTransitions());

        $this->assertEmpty(array_intersect(
            array_map(fn (AppointmentStatus $status): string => $status->value, $reachableFromFree->all()),
            array_map(fn (AppointmentStatus $status): string => $status->value, AppointmentStatus::blocking()),
        ));
    }

    public function test_only_open_appointments_can_be_rescheduled(): void
    {
        $reschedulable = array_filter(
            AppointmentStatus::cases(),
            fn (AppointmentStatus $status): bool => $status->canBeRescheduled(),
        );

        $this->assertSame([AppointmentStatus::Scheduled, AppointmentStatus::Confirmed], array_values($reschedulable));
    }
}
