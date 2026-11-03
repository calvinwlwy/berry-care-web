<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HapusAkunUji extends Command
{
    protected $signature = "app:hapus-akun-uji {--pola=uji\\_%@contoh.com : Pola email SQL LIKE akun uji}";

    protected $description = 'Hapus akun uji dan tokennya setelah konfirmasi';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Perintah ini tidak dapat dijalankan di production.');

            return self::FAILURE;
        }

        $pattern = $this->sqlLikePattern((string) $this->option('pola'));
        $matches = User::query()
            ->whereRaw("email LIKE ? ESCAPE '!'", [$pattern])
            ->orderBy('id')
            ->get();
        $deletable = $matches->reject(fn (User $user): bool => $user->role === 'admin');
        $protectedAdmins = $matches->count() - $deletable->count();

        $this->info("Akun yang cocok: {$matches->count()}.");
        $this->info("Akun non-admin yang akan dihapus: {$deletable->count()}.");
        if ($protectedAdmins > 0) {
            $this->warn("Akun admin dilindungi dan tidak akan dihapus: {$protectedAdmins}.");
        }

        if ($deletable->isEmpty()) {
            $this->info('Tidak ada akun uji yang perlu dihapus.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Hapus semua akun non-admin yang cocok beserta tokennya?', false)) {
            $this->info('Penghapusan dibatalkan.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($deletable): void {
            foreach ($deletable as $user) {
                $user->tokens()->delete();
                $user->delete();
            }
        });

        $this->info("Berhasil menghapus {$deletable->count()} akun uji.");

        return self::SUCCESS;
    }

    private function sqlLikePattern(string $pattern): string
    {
        return str_replace(
            ['!', '\\%', '\\_'],
            ['!!', '!%', '!_'],
            $pattern
        );
    }
}
