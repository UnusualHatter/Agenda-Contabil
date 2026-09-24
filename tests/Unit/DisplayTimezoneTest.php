<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\DisplayTimezone;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DisplayTimezoneTest extends TestCase
{
    public function test_the_application_persists_timestamps_in_utc(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('America/Sao_Paulo', DisplayTimezone::name());
    }

    public function test_stored_timestamps_are_rendered_in_the_local_timezone(): void
    {
        $stored = Carbon::parse('2026-03-10 13:30:00', 'UTC');

        $local = DisplayTimezone::toLocal($stored);

        $this->assertSame('America/Sao_Paulo', $local->timezone->getName());
        $this->assertSame('2026-03-10 10:30:00', $local->format('Y-m-d H:i:s'));
    }

    public function test_user_input_is_interpreted_as_local_time(): void
    {
        $utc = DisplayTimezone::toUtc('2026-03-10 10:30');

        $this->assertSame('UTC', $utc->timezone->getName());
        $this->assertSame('2026-03-10 13:30:00', $utc->format('Y-m-d H:i:s'));
    }
}
