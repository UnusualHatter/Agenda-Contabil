<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceDocument;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_running_twice_does_not_duplicate_anything(): void
    {
        $this->seed(ServiceCatalogSeeder::class);
        $counts = [ServiceCategory::query()->count(), Service::query()->count(), ServiceDocument::query()->count()];

        $this->seed(ServiceCatalogSeeder::class);

        $this->assertSame($counts, [ServiceCategory::query()->count(), Service::query()->count(), ServiceDocument::query()->count()]);
        $this->assertSame([5, 16], array_slice($counts, 0, 2));
    }

    public function test_admin_edits_survive_a_new_seed_run(): void
    {
        $this->seed(ServiceCatalogSeeder::class);
        $irpf = Service::query()->where('slug', 'irpf')->firstOrFail();
        $irpf->update(['active' => false]);
        $irpf->documents()->delete();

        $this->seed(ServiceCatalogSeeder::class);

        $this->assertFalse($irpf->fresh()->active);
        $this->assertSame(0, $irpf->documents()->count());
    }

    public function test_only_the_other_service_asks_for_a_free_description(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $this->assertSame(['outros'], Service::query()->where('requires_details', true)->pluck('slug')->all());
    }
}
