<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_user_can_be_added_and_edited_in_demo_session_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'role' => 'Agronomis',
        ])->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'Pengguna baru berhasil ditambahkan.');

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('Budi Santoso');

        $this->put(route('users.update', 4), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'role' => 'Owner',
        ])->assertRedirect(route('users.index'))
            ->assertSessionHas('status', 'Data pengguna berhasil diperbarui.');
    }

    public function test_user_form_validation_errors_are_returned(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from(route('users.index'))->post(route('users.store'), [
            '_form' => 'create',
            'name' => '',
            'email' => 'invalid-email',
            'role' => 'Unknown',
        ])->assertRedirect(route('users.index'))
            ->assertSessionHasErrors(['name', 'email', 'role'])
            ->assertSessionHas('_old_input._form', 'create');
    }
}
