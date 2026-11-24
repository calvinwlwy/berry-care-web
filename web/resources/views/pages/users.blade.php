@extends('layouts.app')

@section('content')
    <div class="flex flex-col gap-5">
        <section class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="font-display text-[23px] leading-tight font-bold tracking-tight">Manajemen Pengguna</h1>
                <p class="mt-1.5 text-xs text-berry-muted">Kelola akses tim dan peran pengguna dalam sistem Berry Care.</p>
            </div>
            <x-button icon="plus" @click="$dispatch('open-modal', 'add-user')">Tambah Pengguna</x-button>
        </section>

        <x-card>
            <x-section-title title="Daftar Pengguna" description="{{ $totalUsers }} pengguna terdaftar">
                <x-slot:action>
                    <form action="{{ route('users.index') }}" method="GET" class="flex flex-col gap-2 sm:flex-row">
                        <label class="sr-only" for="user-search">Cari nama atau email</label>
                        <input id="user-search" name="q" value="{{ $query }}" placeholder="Cari nama atau email..." class="h-9 min-w-0 rounded-control border border-berry-border px-2.5 text-[11px] text-berry-ink outline-none focus:border-berry-green sm:w-[210px]">
                        <x-select name="role" id="user-role-filter" aria-label="Filter peran" class="h-9 sm:w-[130px]" onchange="this.form.submit()">
                            <option value="">Semua peran</option>
                            <option value="admin" @selected($role === 'admin')>Admin</option>
                            <option value="petani" @selected($role === 'petani')>Petani</option>
                        </x-select>
                        <x-button type="submit" variant="secondary">Cari</x-button>
                    </form>
                </x-slot:action>
            </x-section-title>
            <x-table>
                <thead>
                    <tr class="border-y border-berry-border bg-berry-canvas text-berry-muted">
                        <th class="px-4 py-2.5 font-semibold">Nama</th>
                        <th class="px-4 py-2.5 font-semibold">Email</th>
                        <th class="px-4 py-2.5 font-semibold">Peran</th>
                        <th class="px-4 py-2.5 font-semibold">Tanggal Dibuat</th>
                        <th class="px-4 py-2.5 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php($initials = collect(explode(' ', $user->name))->filter()->map(fn (string $part) => mb_substr($part, 0, 1))->take(2)->join(''))
                        <tr class="border-b border-slate-100 text-berry-slate-700 last:border-0 hover:bg-green-50/40">
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-2">
                                    <span class="grid size-9 place-items-center rounded-full bg-slate-200 text-[10px] font-bold text-berry-slate-700">{{ mb_strtoupper($initials) }}</span>
                                    <strong class="text-berry-ink">{{ $user->name }}</strong>
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3"><x-badge :tone="$user->role === 'admin' ? 'blue' : 'green'">{{ $user->role === 'admin' ? 'Admin' : 'Petani' }}</x-badge></td>
                            <td class="px-4 py-3 text-berry-muted">{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <x-button variant="ghost" @click="$dispatch('open-modal', 'edit-user-{{ $user->id }}')">Edit</x-button>
                                    <x-button variant="ghost" class="text-berry-crimson" @click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')">Hapus</x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-berry-muted">Tidak ada pengguna yang cocok dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </x-table>
            <x-pagination :paginator="$users"/>
        </x-card>

        <x-modal id="add-user" title="Tambah Pengguna" :open="$errors->any() && old('_form') === 'create'">
            <form action="{{ route('users.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="_form" value="create">
                <x-input name="name" id="new-name" label="Nama Lengkap" :value="old('_form') === 'create' ? old('name') : ''" placeholder="Masukkan nama lengkap" :error="old('_form') === 'create' ? $errors->first('name') : null"/>
                <x-input name="email" id="new-email" label="Email" type="email" :value="old('_form') === 'create' ? old('email') : ''" placeholder="nama@contoh.id" :error="old('_form') === 'create' ? $errors->first('email') : null"/>
                <x-input name="password" id="new-password" label="Password" type="password" autocomplete="new-password" :error="old('_form') === 'create' ? $errors->first('password') : null"/>
                <x-select name="role" id="new-role" label="Peran" :error="old('_form') === 'create' ? $errors->first('role') : null">
                    <option value="petani" @selected(old('role', 'petani') === 'petani')>Petani</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </x-select>
                <div class="flex justify-end gap-2 border-t border-berry-border pt-4">
                    <x-button variant="secondary" @click="$dispatch('open-modal-close', 'add-user')">Batal</x-button>
                    <x-button type="submit">Tambah Pengguna</x-button>
                </div>
            </form>
        </x-modal>

        @foreach ($users as $user)
            <x-modal id="edit-user-{{ $user->id }}" title="Edit Pengguna" :open="$errors->any() && old('_form') === 'edit' && (int) old('user_id') === $user->id">
                <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <x-input name="name" id="edit-name-{{ $user->id }}" label="Nama Lengkap" :value="old('_form') === 'edit' && (int) old('user_id') === $user->id ? old('name') : $user->name" placeholder="Masukkan nama lengkap" :error="old('_form') === 'edit' && (int) old('user_id') === $user->id ? $errors->first('name') : null"/>
                    <x-input name="email" id="edit-email-{{ $user->id }}" label="Email" type="email" :value="old('_form') === 'edit' && (int) old('user_id') === $user->id ? old('email') : $user->email" placeholder="nama@contoh.id" :error="old('_form') === 'edit' && (int) old('user_id') === $user->id ? $errors->first('email') : null"/>
                    <x-input name="password" id="edit-password-{{ $user->id }}" label="Password baru (opsional)" type="password" autocomplete="new-password" :error="old('_form') === 'edit' && (int) old('user_id') === $user->id ? $errors->first('password') : null"/>
                    <p class="-mt-2 text-[10px] text-berry-muted">Kosongkan password jika tidak ingin mengubahnya.</p>
                    <x-select name="role" id="edit-role-{{ $user->id }}" label="Peran" :error="old('_form') === 'edit' && (int) old('user_id') === $user->id ? $errors->first('role') : null">
                        <option value="petani" @selected((old('_form') === 'edit' && (int) old('user_id') === $user->id ? old('role') : $user->role) === 'petani')>Petani</option>
                        <option value="admin" @selected((old('_form') === 'edit' && (int) old('user_id') === $user->id ? old('role') : $user->role) === 'admin')>Admin</option>
                    </x-select>
                    <div class="flex justify-end gap-2 border-t border-berry-border pt-4">
                        <x-button variant="secondary" @click="$dispatch('open-modal-close', 'edit-user-{{ $user->id }}')">Batal</x-button>
                        <x-button type="submit">Simpan Perubahan</x-button>
                    </div>
                </form>
            </x-modal>

            <x-modal id="delete-user-{{ $user->id }}" title="Hapus Pengguna" :open="$errors->any() && old('_form') === 'delete' && (int) old('user_id') === $user->id">
                @if (old('_form') === 'delete' && (int) old('user_id') === $user->id && $errors->has('delete'))
                    <p class="mb-4 rounded-control border border-berry-crimson/30 bg-red-50 px-3 py-2 text-xs text-berry-crimson" role="alert">{{ $errors->first('delete') }}</p>
                @endif
                <p class="text-sm leading-relaxed text-berry-slate-600">Hapus akun <strong>{{ $user->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="mt-6 flex justify-end gap-2 border-t border-berry-border pt-4">
                    <x-button variant="secondary" @click="$dispatch('open-modal-close', 'delete-user-{{ $user->id }}')">Batal</x-button>
                    <form action="{{ route('users.destroy', $user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="_form" value="delete">
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <x-button type="submit" variant="danger">Hapus Pengguna</x-button>
                    </form>
                </div>
            </x-modal>
        @endforeach
    </div>
@endsection
