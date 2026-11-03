<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_climate_summary_and_current_date(): void
    {
        $today = Carbon::now()->locale('id')->translatedFormat('j F Y');

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mikroklimat 24 Jam')
            ->assertSee('id="climate-chart"', false)
            ->assertSee($today)
            ->assertSee('Selamat pagi, Pak Dadang');
    }

    public function test_dashboard_search_filters_recent_polybag_rows(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('dashboard', ['q' => 'POT-08']))
            ->assertOk()
            ->assertSee('POT-08')
            ->assertDontSee('POT-01');
    }
}
