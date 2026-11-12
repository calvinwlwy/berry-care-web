<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\ClimateReading;
use App\Models\Diagnosis;
use App\Models\Polybag;
use App\Models\SensorReading;
use App\Models\Threshold;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BerryCareApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_routes_require_sanctum_authentication_and_return_json_not_found(): void
    {
        $paths = [
            ['GET', '/api/polybags'],
            ['GET', '/api/climate'],
            ['POST', '/api/climate'],
            ['GET', '/api/diagnoses'],
            ['GET', '/api/alerts'],
            ['GET', '/api/thresholds'],
            ['GET', '/api/dashboard/summary'],
        ];
        foreach ($paths as [$method, $uri]) {
            $this->json($method, $uri)->assertUnauthorized()
                ->assertExactJson(['message' => 'Tidak terautentikasi.']);
        }

        $this->getJson('/api/not-a-domain-route')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);

        $this->putJson('/api/thresholds')
            ->assertUnauthorized()
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_polybag_admin_crud_petani_read_access_filters_and_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->create(['role' => 'petani']);
        $first = Polybag::factory()->create(['code' => 'POT-01', 'block' => 'A']);
        Polybag::factory()->create(['code' => 'POT-02', 'block' => 'B']);
        Polybag::factory()->create(['code' => 'POT-03', 'block' => 'A', 'is_active' => false]);

        $farmerToken = $farmer->createToken('test')->plainTextToken;
        $this->withToken($farmerToken)
            ->getJson('/api/polybags?q=POT-01&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'POT-01')
            ->assertJsonPath('meta.per_page', 1);

        $this->getJson('/api/polybags?block=A&is_active=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'unknown');

        $this->postJson('/api/polybags', [
            'code' => 'POT-04', 'block' => 'A',
        ])->assertForbidden()->assertExactJson(['message' => 'Akses ditolak.']);

        Auth::forgetGuards();
        $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson('/api/polybags', ['code' => 'invalid', 'block' => 'Z'])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.')
            ->assertJsonStructure(['errors' => ['code', 'block']]);

        $created = $this->postJson('/api/polybags', [
            'code' => 'POT-04', 'block' => 'C', 'location' => 'Bedeng 4',
        ])->assertCreated()
            ->assertJsonPath('data.code', 'POT-04');
        $createdId = $created->json('data.id');

        $this->putJson("/api/polybags/{$createdId}", ['block' => 'B'])
            ->assertOk()
            ->assertJsonPath('data.block', 'B');
        $this->getJson('/api/polybags/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);
        $this->deleteJson("/api/polybags/{$createdId}")->assertNoContent();

        Auth::forgetGuards();
        $this->withToken($farmerToken)
            ->putJson("/api/polybags/{$first->id}", ['block' => 'C'])
            ->assertForbidden();
    }

    public function test_sensor_readings_are_validated_evaluated_deduplicated_and_auto_resolved(): void
    {
        $farmer = User::factory()->create(['role' => 'petani']);
        $token = $farmer->createToken('test')->plainTextToken;
        $polybag = Polybag::factory()->create(['code' => 'POT-10']);
        $low = [
            'soil_moisture' => 20,
            'nitrogen' => 10,
            'phosphorus' => 5,
            'potassium' => 50,
            'recorded_at' => now()->toISOString(),
        ];

        $this->withToken($token)->postJson("/api/polybags/{$polybag->id}/readings", [
            ...$low, 'soil_moisture' => 101,
        ])->assertUnprocessable()
            ->assertJsonPath('errors.soil_moisture.0', 'Kadar air tanah harus 0 sampai 100 persen.');

        $this->postJson("/api/polybags/{$polybag->id}/readings", $low)
            ->assertCreated()
            ->assertJsonPath('data.polybag_id', $polybag->id);
        $this->postJson("/api/polybags/{$polybag->id}/readings", $low)->assertCreated();
        $this->assertSame(4, Alert::query()->where('polybag_id', $polybag->id)->where('status', 'active')->count());

        $this->getJson("/api/polybags/{$polybag->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'deficient')
            ->assertJsonCount(4, 'data.violations');

        $normal = [
            'soil_moisture' => 60,
            'nitrogen' => 40,
            'phosphorus' => 25,
            'potassium' => 140,
            'recorded_at' => now()->addSecond()->toISOString(),
        ];
        $this->postJson("/api/polybags/{$polybag->id}/readings", $normal)->assertCreated();
        $this->assertSame(4, Alert::query()->where('polybag_id', $polybag->id)->where('status', 'resolved')->count());

        $this->getJson("/api/polybags/{$polybag->id}/readings?per_page=1")
            ->assertOk()
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonCount(1, 'data');
        $this->getJson('/api/polybags/999999/readings')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);

        $this->postJson("/api/polybags/{$polybag->id}/readings", [
            ...$normal, 'recorded_at' => now()->addMinutes(6)->toISOString(),
        ])->assertUnprocessable();
    }

    public function test_climate_readings_create_dedupe_and_resolve_humidity_alerts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/climate', [
            'temperature' => 24, 'humidity' => 90, 'recorded_at' => now()->toISOString(),
        ])->assertCreated();
        $this->postJson('/api/climate', [
            'temperature' => 24, 'humidity' => 91, 'recorded_at' => now()->addSecond()->toISOString(),
        ])->assertCreated();
        $this->assertSame(1, Alert::query()->where('type', 'humidity_high')->where('status', 'active')->count());

        $this->postJson('/api/climate', [
            'temperature' => 22, 'humidity' => 70, 'recorded_at' => now()->addSeconds(2)->toISOString(),
        ])->assertCreated();
        $this->assertSame(1, Alert::query()->where('type', 'humidity_high')->where('status', 'resolved')->count());

        $this->getJson('/api/climate/latest')
            ->assertOk()
            ->assertJsonPath('data.humidity', 70);
        $this->getJson('/api/climate?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data');
        $this->postJson('/api/climate', [
            'temperature' => 61, 'humidity' => 50, 'recorded_at' => now()->toISOString(),
        ])->assertUnprocessable()
            ->assertJsonPath('errors.temperature.0', 'Suhu harus -10 sampai 60 derajat Celsius.');
    }

    public function test_diagnosis_crud_filters_policy_file_upload_and_user_identity(): void
    {
        Storage::fake('public');
        $farmer = User::factory()->create(['role' => 'petani']);
        $admin = User::factory()->create(['role' => 'admin']);
        $polybag = Polybag::factory()->create();
        $token = $farmer->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/diagnoses', [
            'polybag_id' => $polybag->id,
            'user_id' => $admin->id,
            'type' => 'leaf',
            'label' => 'Bercak daun',
            'confidence' => 94.8,
            'severity' => 'watch',
            'scanned_at' => now()->toISOString(),
            'image' => UploadedFile::fake()->image('leaf.jpg'),
        ])->assertCreated()
            ->assertJsonPath('data.user_id', $farmer->id)
            ->assertJsonPath('data.type', 'leaf')
            ->assertJsonMissingPath('data.password');

        $diagnosis = Diagnosis::query()->firstOrFail();
        $this->assertNotNull($diagnosis->image_path);
        Storage::disk('public')->assertExists($diagnosis->image_path);

        $this->getJson('/api/diagnoses?type=leaf&polybag_id='.$polybag->id.'&per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1);
        $this->putJson("/api/diagnoses/{$diagnosis->id}", ['label' => 'Ubah petani'])
            ->assertForbidden();

        $adminToken = $admin->createToken('test')->plainTextToken;
        Auth::forgetGuards();
        $this->withToken($adminToken)->putJson("/api/diagnoses/{$diagnosis->id}", [
            'severity' => 'action',
        ])->assertOk()->assertJsonPath('data.severity', 'action');
        $this->getJson('/api/diagnoses/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);
        $this->deleteJson("/api/diagnoses/{$diagnosis->id}")->assertNoContent();
        Storage::disk('public')->assertMissing($diagnosis->image_path);
    }

    public function test_alerts_allow_read_and_resolve_for_petani_but_manual_create_and_delete_are_admin_only(): void
    {
        $farmer = User::factory()->create(['role' => 'petani']);
        $admin = User::factory()->create(['role' => 'admin']);
        $polybag = Polybag::factory()->create();
        $alert = Alert::factory()->create(['polybag_id' => $polybag->id, 'severity' => 'warning']);
        $farmerToken = $farmer->createToken('test')->plainTextToken;
        $this->withToken($farmerToken)->getJson('/api/alerts?status=active&severity=warning')
            ->assertOk()
            ->assertJsonPath('data.0.id', $alert->id);
        $this->postJson('/api/alerts', [
            'type' => 'manual', 'severity' => 'info', 'message' => 'Catatan petani',
        ])->assertForbidden();
        $this->deleteJson("/api/alerts/{$alert->id}")->assertForbidden();
        $this->patchJson("/api/alerts/{$alert->id}/resolve")
            ->assertOk()
            ->assertJsonPath('data.status', 'resolved')
            ->assertJsonPath('data.resolved_by', $farmer->id);

        $adminToken = $admin->createToken('test')->plainTextToken;
        Auth::forgetGuards();
        $this->withToken($adminToken)->postJson('/api/alerts', [
            'type' => 'manual', 'severity' => 'info', 'message' => 'Peringatan manual',
        ])->assertCreated()
            ->assertJsonPath('data.source', 'manual');
        $this->withToken($adminToken)->deleteJson("/api/alerts/{$alert->id}")->assertNoContent();
        $this->getJson('/api/alerts/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);
    }

    public function test_thresholds_are_readable_by_petani_and_mutable_only_by_admin(): void
    {
        $farmer = User::factory()->create(['role' => 'petani']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->withToken($farmer->createToken('test')->plainTextToken)
            ->getJson('/api/thresholds')
            ->assertOk()
            ->assertJsonCount(5, 'data');
        $this->putJson('/api/thresholds', ['thresholds' => ['humidity_max' => 90]])
            ->assertForbidden();

        $adminToken = $admin->createToken('test')->plainTextToken;
        Auth::forgetGuards();
        $this->withToken($adminToken)->putJson('/api/thresholds', [
            'thresholds' => ['humidity_max' => 90, 'soil_moisture_min' => 50],
        ])->assertOk()
            ->assertJsonPath('message', 'Ambang batas berhasil diperbarui.');
        $this->assertSame(90.0, Threshold::query()->where('key', 'humidity_max')->value('value') + 0.0);
        $this->putJson('/api/thresholds', ['thresholds' => ['humidity_max' => 101]])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Data yang diberikan tidak valid.');
    }

    public function test_dashboard_summary_reports_current_domain_counts_and_seven_day_average(): void
    {
        $user = User::factory()->create(['role' => 'petani']);
        $token = $user->createToken('test')->plainTextToken;
        $polybag = Polybag::factory()->create(['is_active' => true]);
        SensorReading::factory()->create([
            'polybag_id' => $polybag->id,
            'soil_moisture' => 60,
            'nitrogen' => 40,
            'phosphorus' => 25,
            'potassium' => 140,
            'recorded_at' => now(),
        ]);
        ClimateReading::factory()->create(['temperature' => 22, 'humidity' => 70, 'recorded_at' => now()]);
        Diagnosis::factory()->create([
            'user_id' => $user->id,
            'polybag_id' => null,
            'scanned_at' => now(),
        ]);
        Alert::factory()->create([
            'polybag_id' => null,
            'severity' => 'critical',
            'status' => 'active',
        ]);

        $this->withToken($token)->getJson('/api/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.active_polybags', 1)
            ->assertJsonPath('data.polybags_by_status.optimal', 1)
            ->assertJsonPath('data.active_alerts_by_severity.critical', 1)
            ->assertJsonPath('data.latest_climate.humidity', 70)
            ->assertJsonPath('data.diagnoses_today', 1)
            ->assertJsonPath('data.average_soil_moisture_last_7_days.6.average_soil_moisture', 60);
    }

    public function test_demo_seeder_is_repeatable_and_keeps_threshold_defaults(): void
    {
        Artisan::call('db:seed', ['--class' => 'BerryCareDemoSeeder']);
        Artisan::call('db:seed', ['--class' => 'BerryCareDemoSeeder']);

        $this->assertDatabaseCount('polybags', 8);
        $this->assertDatabaseCount('sensor_readings', 16);
        $this->assertDatabaseCount('climate_readings', 3);
        $this->assertDatabaseCount('diagnoses', 3);
        $this->assertDatabaseCount('alerts', 2);
        $this->assertDatabaseCount('thresholds', 5);
        $this->assertDatabaseHas('thresholds', ['key' => 'humidity_max', 'value' => 85]);
    }

    public function test_api_general_rate_limit_is_sixty_requests_per_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'petani']);
        $token = $user->createToken('test')->plainTextToken;
        RateLimiter::clear('api-user|'.$user->id);
        $this->withToken($token);

        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->getJson('/api/thresholds')->assertOk();
        }
        $this->getJson('/api/thresholds')->assertTooManyRequests();

        RateLimiter::clear('api-user|'.$user->id);
    }
}
