<?php

namespace Tests\Feature;

use App\Models\Diagnosis;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KategoriApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_kategori_routes_require_authentication_and_return_json_errors(): void
    {
        $this->getJson('/api/kategori')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);

        $this->getJson('/api/kategori/999999')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Tidak terautentikasi.']);

        $this->patchJson('/api/kategori', [])
            ->assertMethodNotAllowed()
            ->assertExactJson(['message' => 'Metode HTTP tidak diizinkan.']);

        $token = User::factory()->create(['role' => 'admin'])->createToken('test')->plainTextToken;
        $this->withToken($token)->getJson('/api/kategori/bukan-angka')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);

        Route::get('/api/test-server-failure', fn () => throw new \RuntimeException('detail-rahasia-stack-trace'));
        $this->getJson('/api/test-server-failure')
            ->assertInternalServerError()
            ->assertExactJson(['message' => 'Terjadi kesalahan pada server.'])
            ->assertDontSee('detail-rahasia-stack-trace');
    }

    public function test_kategori_index_searches_paginates_and_includes_diagnosis_count_for_all_users(): void
    {
        $farmer = User::factory()->create(['role' => 'petani']);
        $first = Kategori::factory()->create(['nama_kategori' => 'Penyakit Daun']);
        Kategori::factory()->create(['nama_kategori' => 'Penyakit Buah']);
        Kategori::factory()->create(['nama_kategori' => 'Hama']);
        Diagnosis::factory()->count(2)->create(['kategori_id' => $first->id]);

        $this->withToken($farmer->createToken('test')->plainTextToken)
            ->getJson('/api/kategori?q=Penyakit&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.nama_kategori', 'Penyakit Buah')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.diagnoses_count', 0);

        $this->getJson('/api/kategori?q=Daun')
            ->assertOk()
            ->assertJsonPath('data.0.diagnoses_count', 2);

        Auth::forgetGuards();
        $this->withToken(User::factory()->create(['role' => 'admin'])->createToken('test')->plainTextToken)
            ->getJson("/api/kategori/{$first->id}")
            ->assertOk()
            ->assertJsonPath('data.diagnoses_count', 2);
    }

    public function test_admin_can_create_update_and_delete_categories_with_normalized_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;

        $created = $this->withToken($token)->postJson('/api/kategori', [
            'nama_kategori' => '  Kategori Baru  ',
            'deskripsi' => '  Deskripsi awal  ',
            'role' => 'petani',
        ])->assertCreated()
            ->assertJsonPath('message', 'Kategori berhasil ditambahkan.')
            ->assertJsonPath('data.nama_kategori', 'Kategori Baru')
            ->assertJsonPath('data.deskripsi', 'Deskripsi awal');

        $kategoriId = $created->json('data.id');
        $this->assertSame('admin', $admin->fresh()->role);

        $this->putJson("/api/kategori/{$kategoriId}", [
            'nama_kategori' => '  Kategori Diubah  ',
            'deskripsi' => '  Deskripsi baru  ',
        ])->assertOk()
            ->assertJsonPath('message', 'Kategori berhasil diperbarui.')
            ->assertJsonPath('data.nama_kategori', 'Kategori Diubah')
            ->assertJsonPath('data.deskripsi', 'Deskripsi baru');

        $this->deleteJson("/api/kategori/{$kategoriId}")->assertNoContent();
        $this->assertDatabaseMissing('kategori', ['id' => $kategoriId]);
    }

    public function test_petani_cannot_create_update_or_delete_categories(): void
    {
        $farmer = User::factory()->create(['role' => 'petani']);
        $kategori = Kategori::factory()->create();

        $this->withToken($farmer->createToken('test')->plainTextToken)
            ->postJson('/api/kategori', ['nama_kategori' => 'Kategori Petani'])
            ->assertForbidden()
            ->assertExactJson(['message' => 'Akses ditolak.']);

        $this->putJson("/api/kategori/{$kategori->id}", ['nama_kategori' => 'Perubahan Petani'])
            ->assertForbidden()
            ->assertExactJson(['message' => 'Akses ditolak.']);

        $this->deleteJson("/api/kategori/{$kategori->id}")
            ->assertForbidden()
            ->assertExactJson(['message' => 'Akses ditolak.']);
    }

    public function test_kategori_store_validates_required_length_duplicates_and_description_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;
        $existing = Kategori::factory()->create(['nama_kategori' => 'Penyakit Daun']);

        $this->withToken($token)->postJson('/api/kategori', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.nama_kategori.0', 'Nama kategori wajib diisi.');

        $this->postJson('/api/kategori', ['nama_kategori' => 'X'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.nama_kategori.0', 'Nama kategori minimal 2 karakter.');

        $this->postJson('/api/kategori', ['nama_kategori' => str_repeat('A', 101)])
            ->assertUnprocessable()
            ->assertJsonPath('errors.nama_kategori.0', 'Nama kategori maksimal 100 karakter.');

        $this->postJson('/api/kategori', ['nama_kategori' => $existing->nama_kategori])
            ->assertUnprocessable()
            ->assertJsonPath('errors.nama_kategori.0', 'Nama kategori sudah digunakan.');

        $this->postJson('/api/kategori', [
            'nama_kategori' => 'Kategori Valid',
            'deskripsi' => ['bukan', 'teks'],
        ])->assertUnprocessable()
            ->assertJsonPath('errors.deskripsi.0', 'Deskripsi harus berupa teks.');
    }

    public function test_kategori_update_accepts_its_current_name_but_rejects_another_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->withToken($admin->createToken('test')->plainTextToken);
        $first = Kategori::factory()->create(['nama_kategori' => 'Penyakit Daun']);
        $second = Kategori::factory()->create(['nama_kategori' => 'Penyakit Buah']);

        $this->patchJson("/api/kategori/{$first->id}", [
            'nama_kategori' => '  Penyakit Daun  ',
        ])->assertOk()
            ->assertJsonPath('data.nama_kategori', 'Penyakit Daun');

        $this->putJson("/api/kategori/{$first->id}", [
            'nama_kategori' => $second->nama_kategori,
        ])->assertUnprocessable()
            ->assertJsonPath('errors.nama_kategori.0', 'Nama kategori sudah digunakan.');
    }

    public function test_used_category_cannot_be_deleted_but_unused_category_can(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;
        $used = Kategori::factory()->create();
        $unused = Kategori::factory()->create();
        Diagnosis::factory()->create(['kategori_id' => $used->id]);

        $this->withToken($token)->deleteJson("/api/kategori/{$used->id}")
            ->assertConflict()
            ->assertExactJson([
                'message' => 'Kategori masih dipakai oleh data diagnosis dan tidak dapat dihapus.',
            ]);
        $this->assertDatabaseHas('kategori', ['id' => $used->id]);

        $this->deleteJson("/api/kategori/{$unused->id}")->assertNoContent();
        $this->assertDatabaseMissing('kategori', ['id' => $unused->id]);

        $this->deleteJson('/api/kategori/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Data tidak ditemukan.']);
    }

    public function test_diagnosis_can_use_and_filter_categories_and_reject_invalid_category_ids(): void
    {
        $farmer = User::factory()->create(['role' => 'petani']);
        $token = $farmer->createToken('test')->plainTextToken;
        $kategori = Kategori::factory()->create(['nama_kategori' => 'Hama']);

        $payload = [
            'kategori_id' => $kategori->id,
            'type' => 'leaf',
            'label' => 'Daun terserang hama',
            'confidence' => 91,
            'severity' => 'watch',
            'scanned_at' => now()->toISOString(),
        ];

        $created = $this->withToken($token)->postJson('/api/diagnoses', $payload)
            ->assertCreated()
            ->assertJsonPath('data.kategori_id', $kategori->id)
            ->assertJsonPath('data.kategori.id', $kategori->id)
            ->assertJsonPath('data.kategori.nama_kategori', 'Hama');
        $diagnosisId = $created->json('data.id');

        $this->getJson("/api/diagnoses?kategori_id={$kategori->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kategori.nama_kategori', 'Hama');

        $secondKategori = Kategori::factory()->create();
        $this->putJson("/api/diagnoses/{$diagnosisId}", ['kategori_id' => $secondKategori->id])
            ->assertForbidden();

        $this->postJson('/api/diagnoses', [...$payload, 'kategori_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonPath('errors.kategori_id.0', 'Kategori diagnosis yang dipilih tidak ditemukan.');

        Auth::forgetGuards();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->withToken($admin->createToken('test')->plainTextToken)
            ->putJson("/api/diagnoses/{$diagnosisId}", ['kategori_id' => $secondKategori->id])
            ->assertOk()
            ->assertJsonPath('data.kategori_id', $secondKategori->id)
            ->assertJsonPath('data.kategori.nama_kategori', $secondKategori->nama_kategori);

        $this->putJson("/api/diagnoses/{$diagnosisId}", ['kategori_id' => 999999])
            ->assertUnprocessable()
            ->assertJsonPath('errors.kategori_id.0', 'Kategori diagnosis yang dipilih tidak ditemukan.');
    }

    public function test_kategori_seeder_is_idempotent_and_polybag_create_returns_database_default(): void
    {
        Artisan::call('db:seed', ['--class' => 'KategoriSeeder']);
        Artisan::call('db:seed', ['--class' => 'KategoriSeeder']);

        $this->assertDatabaseCount('kategori', 5);
        $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Penyakit Daun']);
        $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Penyakit Buah']);
        $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Hama']);
        $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Defisiensi Nutrisi']);
        $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Tanaman Sehat']);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson('/api/polybags', ['code' => 'POT-DEFAULT', 'block' => 'A'])
            ->assertCreated()
            ->assertJsonPath('data.is_active', true);
    }
}
