<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ManagementPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_each_management_screen(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('polybags.index'))->assertOk()->assertSee('Data Telemetri Polybag');
        $this->get(route('diagnosis.index'))->assertOk()->assertSee('Riwayat Diagnosis AI');
        $this->get(route('alerts.index'))->assertOk()->assertSee('Early Warning & Alerts');
        $this->get(route('thresholds.index'))->assertOk()->assertSee('Konfigurasi Threshold');
        $this->get(route('users.index'))->assertOk()->assertSee('Manajemen Pengguna');
    }

    public function test_polybag_list_search_and_block_filters_work(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('polybags.index', ['q' => 'POT-05', 'block' => 'Blok B']))
            ->assertOk()
            ->assertSee('POT-05')
            ->assertDontSee('POT-01');

        $this->get(route('polybags.index', ['block' => 'Blok C']))
            ->assertOk()
            ->assertSee('POT-07')
            ->assertDontSee('POT-01');
    }

    public function test_diagnosis_filter_and_search_work(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('diagnosis.index', ['filter' => 'Jamur', 'q' => 'POT-05']))
            ->assertOk()
            ->assertSee('Bercak Daun')
            ->assertDontSee('Buah Matang');
    }

    public function test_threshold_values_are_validated_and_saved_in_session(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->put(route('thresholds.update'), [
            'humidity' => 82,
            'water' => 40,
            'n' => 31,
            'p' => 21,
            'k' => 121,
        ])->assertRedirect()
            ->assertSessionHas('dummy.thresholds.humidity', 82);

        $this->put(route('thresholds.update'), [
            'humidity' => 120,
            'water' => 40,
            'n' => 31,
            'p' => 21,
            'k' => 121,
        ])->assertSessionHasErrors('humidity');
    }

    public function test_alert_count_matches_dummy_alerts_and_resets_on_a_new_request(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->withSession(['dummy.done-alerts' => [1, 2]])
            ->get(route('alerts.index'))
            ->assertOk()
            ->assertSee('x-text="openCount"', false)
            ->assertSee('activeAlerts: 3', false);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('activeAlerts: 3', false);
    }

    public function test_diagnosis_history_remains_available_without_manual_upload(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('diagnosis.index'))
            ->assertOk()
            ->assertSee('Riwayat Diagnosis AI')
            ->assertSee('Bercak Daun')
            ->assertDontSee('Upload Foto Manual');

        $this->assertFalse(Route::has('diagnosis.upload'));
    }
}
