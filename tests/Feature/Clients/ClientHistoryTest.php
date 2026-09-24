<?php

declare(strict_types=1);

namespace Tests\Feature\Clients;

use App\Domain\Clients\Queries\ClientHistory;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-10 12:00');
        $this->client = Client::factory()->create();
    }

    public function test_history_only_lists_the_clients_own_appointments(): void
    {
        $own = $this->appointmentAt('2026-10-01 13:00');
        Appointment::factory()->between('2026-10-02 13:00', '2026-10-02 14:00')->create();

        $this->assertSame([$own->id], ClientHistory::past($this->client)->modelKeys());
        $this->assertCount(0, ClientHistory::upcoming($this->client));
    }

    public function test_past_is_newest_first_and_upcoming_is_soonest_first(): void
    {
        $older = $this->appointmentAt('2026-09-01 13:00');
        $recent = $this->appointmentAt('2026-10-01 13:00');
        $later = $this->appointmentAt('2026-11-01 13:00');
        $sooner = $this->appointmentAt('2026-10-15 13:00');

        $this->assertSame([$recent->id, $older->id], ClientHistory::past($this->client)->modelKeys());
        $this->assertSame([$sooner->id, $later->id], ClientHistory::upcoming($this->client)->modelKeys());
    }

    public function test_deactivated_services_are_still_shown(): void
    {
        $service = Service::factory()->create(['name' => 'Orçamento empresarial']);
        $this->appointmentAt('2026-10-01 13:00', $service);

        $service->update(['active' => false]);

        $this->assertSame('Orçamento empresarial', ClientHistory::past($this->client)->first()->service->name);
    }

    private function appointmentAt(string $start, ?Service $service = null): Appointment
    {
        return Appointment::factory()
            ->for($this->client)
            ->for($service ?? Service::factory()->create())
            ->between($start, now()->parse($start)->addHour()->format('Y-m-d H:i'))
            ->create();
    }
}
