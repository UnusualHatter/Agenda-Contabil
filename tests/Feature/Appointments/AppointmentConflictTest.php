<?php

declare(strict_types=1);

namespace Tests\Feature\Appointments;

use App\Domain\Appointments\Actions\ScheduleAppointment;
use App\Domain\Appointments\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AppointmentConflictTest extends TestCase
{
    use RefreshDatabase;

    private User $responsible;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responsible = User::factory()->create(['name' => 'Maria']);

        Appointment::factory()
            ->for($this->responsible, 'responsible')
            ->between('2026-10-05 13:00', '2026-10-05 14:00')
            ->create();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function overlappingPeriods(): array
    {
        return [
            'partial overlap at the start' => ['2026-10-05 12:30', '2026-10-05 13:30'],
            'partial overlap at the end' => ['2026-10-05 13:30', '2026-10-05 14:30'],
            'inside the existing one' => ['2026-10-05 13:15', '2026-10-05 13:45'],
            'containing the existing one' => ['2026-10-05 12:00', '2026-10-05 15:00'],
            'exactly the same period' => ['2026-10-05 13:00', '2026-10-05 14:00'],
        ];
    }

    #[DataProvider('overlappingPeriods')]
    public function test_overlapping_periods_are_blocked(string $start, string $end): void
    {
        try {
            $this->schedule($this->responsible, $start, $end);
            $this->fail('An overlapping appointment was accepted.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Maria já tem atendimento das 10:00 às 11:00. Escolha outro horário ou outro responsável.',
                $exception->errors()['starts_at'][0],
            );
        }

        $this->assertSame(1, Appointment::query()->count());
    }

    public function test_appointments_are_created_when_there_is_no_conflict(): void
    {
        $this->schedule($this->responsible, '2026-10-05 16:00', '2026-10-05 17:00');

        $this->assertSame(2, Appointment::query()->count());
    }

    public function test_adjacent_appointments_are_allowed(): void
    {
        $this->schedule($this->responsible, '2026-10-05 12:00', '2026-10-05 13:00');
        $this->schedule($this->responsible, '2026-10-05 14:00', '2026-10-05 15:00');

        $this->assertSame(3, Appointment::query()->count());
    }

    public function test_different_responsibles_may_share_the_same_period(): void
    {
        $this->schedule(User::factory()->create(), '2026-10-05 13:00', '2026-10-05 14:00');

        $this->assertSame(2, Appointment::query()->count());
    }

    /**
     * @return array<string, array{AppointmentStatus}>
     */
    public static function freeStatuses(): array
    {
        return [
            'cancelled' => [AppointmentStatus::Cancelled],
            'no show' => [AppointmentStatus::NoShow],
            'completed' => [AppointmentStatus::Completed],
        ];
    }

    #[DataProvider('freeStatuses')]
    public function test_appointments_that_no_longer_block_free_the_period(AppointmentStatus $status): void
    {
        Appointment::query()->update(['status' => $status]);

        $this->schedule($this->responsible, '2026-10-05 13:00', '2026-10-05 14:00');

        $this->assertSame(2, Appointment::query()->count());
    }

    public function test_the_database_refuses_overlaps_that_bypass_the_application(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('appointments_no_overlap');

        Appointment::factory()
            ->for($this->responsible, 'responsible')
            ->between('2026-10-05 13:30', '2026-10-05 14:30')
            ->create();
    }

    public function test_the_end_must_come_after_the_start(): void
    {
        $this->expectException(ValidationException::class);

        $this->schedule($this->responsible, '2026-10-05 18:00', '2026-10-05 18:00');
    }

    private function schedule(User $responsible, string $start, string $end): Appointment
    {
        return app(ScheduleAppointment::class)->handle($responsible, [
            'client_id' => Client::factory()->create()->id,
            'service_id' => Service::factory()->create()->id,
            'responsible_user_id' => $responsible->id,
            'starts_at' => Carbon::parse($start, 'UTC'),
            'ends_at' => Carbon::parse($end, 'UTC'),
        ]);
    }
}
