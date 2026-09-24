<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoAgendaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAgendaSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_full_seed_builds_a_demo_agenda_through_the_real_rules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(16, Appointment::query()->count());
        $this->assertGreaterThan(0, Appointment::query()->has('documents')->count());
    }

    public function test_it_never_adds_to_an_agenda_that_already_has_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->seed(DemoAgendaSeeder::class);

        $this->assertSame(16, Appointment::query()->count());
    }
}
