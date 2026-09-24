<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_theme_is_decided_before_the_stylesheet_loads(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $themeScript = strpos($html, 'document.documentElement.dataset.theme');
        $stylesheet = strpos($html, 'rel="stylesheet"');

        $this->assertNotFalse($themeScript);
        $this->assertLessThan($stylesheet, $themeScript);
    }

    public function test_the_theme_toggle_is_available_before_and_after_login(): void
    {
        $this->get('/login')->assertSee('Seguir o sistema');

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertSee('Seguir o sistema');
    }

    public function test_the_dashboard_greets_by_first_name(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'Ana Paula Souza']))
            ->get('/dashboard')
            ->assertSee('Olá, Ana');
    }
}
