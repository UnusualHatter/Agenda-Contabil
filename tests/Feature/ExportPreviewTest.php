<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportPreviewTest extends TestCase
{
    use RefreshDatabase;

    private string $output = 'storage/framework/testing/preview';

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory(base_path($this->output));

        parent::tearDown();
    }

    public function test_the_preview_is_rendered_for_the_published_address(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->artisan('preview:export', ['url' => 'https://example.github.io/Agenda', '--output' => $this->output])
            ->assertSuccessful();

        $dashboard = file_get_contents(base_path("{$this->output}/dashboard/index.html"));
        $feed = file_get_contents(base_path("{$this->output}/agenda/eventos.json"));

        $this->assertStringContainsString('href="https://example.github.io/Agenda/agenda"', $dashboard);
        $this->assertStringNotContainsString('http://example.github.io', $dashboard.$feed);
        $this->assertStringContainsString('data-preview', $dashboard);
        $this->assertFileExists(base_path("{$this->output}/index.html"));
        $this->assertStringContainsString('"editable":false', $feed);
    }

    public function test_it_refuses_to_publish_from_a_production_database(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->artisan('preview:export', ['url' => 'https://example.github.io/Agenda', '--output' => $this->output])
            ->assertFailed();

        $this->assertDirectoryDoesNotExist(base_path($this->output));
    }
}
