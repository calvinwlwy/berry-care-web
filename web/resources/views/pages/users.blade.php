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
                    <form action="{{ route('users.index') }}" method="GET" class="flex h-9 items-center gap-2 rounded-control border border-berry-border px-2.5 text-berry-slate-400 sm:w-[210px]">
                        <x-icon name="search"/>
                        <input name="q" value="{{ $query }}" placeholder="Cari pengguna..." class="min-w-0 flex-1 bg-transparent text-[11px] text-berry-ink outline-none">
                    </form>
                </x-slot:action>
            </x-section-title>
            <x-table>
                <thead>
                    <tr class="border-y border-berry-border bg-berry-canvas text-berry-muted">
                        <th class="px-4 py-2.5 font-semibold">Nama</th><th class="px-4 py-2.5 font-semibold">Email</th><th class="px-4 py-2.5 font-semibold">Peran</th><th class="px-4 py-2.5 font-semibold">Status</th><th class="px-4 py-2.5 font-semibold">Aktivitas Terakhir</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-slate-100 text-berry-slate-700 last:border-0 hover:bg-green-50/40">
                            <td class="px-4 py-3"><span class="flex items-center gap-2"><span class="grid size-9 place-items-center rounded-full bg-slate-200 text-[10px] font-bold text-berry-slate-700">{{ $user['initials'] }}</span><strong class="text-berry-ink">{{ $user['name'] }}</strong></span></td>
                            <td class="px-4 py-3">{{ $user['email'] }}</td>
                            <td class="px-4 py-3"><x-badge :tone="match($user['role']) { 'Owner' => 'blue', 'Agronomis' => 'green', default => 'slate' }">{{ $user['role'] }}</x-badge></td>
                            <td class="px-4 py-3"><span class="flex items-center gap-1.5 font-semibold text-berry-green-dark"><i class="size-1.5 rounded-full bg-berry-green-bright"></i>Aktif</span></td>
                            <td class="px-4 py-3 text-berry-muted">{{ $user['last'] }}</td>
                            <td class="px-4 py-3"><x-button variant="ghost" @click="$dispatch('open-modal', 'edit-user-{{ $user['id'] }}')">Edit</x-button></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-berry-muted">Tidak ada pengguna yang cocok dengan pencarian.</td></tr>
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
                <x-input name="email" id="new-email" label="Email" type="email" :value="old('_form') === 'create' ? old('email') : ''" placeholder="nama@haifresh.id" :error="old('_form') === 'create' ? $errors->first('email') : null"/>
                <x-select name="role" id="new-role" label="Peran" :error="old('_form') === 'create' ? $errors->first('role') : null">
                    <option value="Petani Lapangan" @selected(old('role', 'Petani Lapangan') === 'Petani Lapangan')>Petani Lapangan</option><option value="Owner" @selected(old('role') === 'Owner')>Owner</option><option value="Agronomis" @selected(old('role') === 'Agronomis')>Agronomis</option>
                </x-select>
                <div class="flex justify-end gap-2 border-t border-berry-border pt-4">
                    <x-button variant="secondary" @click="$dispatch('open-modal-close', 'add-user')">Batal</x-button>
                    <x-button type="submit">Tambah Pengguna</x-button>
                </div>
            </form>
        </x-modal>

        @foreach ($users as $user)
            <x-modal id="edit-user-{{ $user['id'] }}" title="Edit Pengguna" :open="$errors->any() && old('_form') === 'edit' && (int) old('user_id') === $user['id']">
                <form action="{{ route('users.update', $user['id']) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit">
                    <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                    <x-input name="name" id="edit-name-{{ $user['id'] }}" label="Nama Lengkap" :value="old('_form') === 'edit' && (int) old('user_id') === $user['id'] ? old('name') : $user['name']" placeholder="Masukkan nama lengkap" :error="old('_form') === 'edit' && (int) old('user_id') === $user['id'] ? $errors->first('name') : null"/>
                    <x-input name="email" id="edit-email-{{ $user['id'] }}" label="Email" type="email" :value="old('_form') === 'edit' && (int) old('user_id') === $user['id'] ? old('email') : $user['email']" placeholder="nama@haifresh.id" :error="old('_form') === 'edit' && (int) old('user_id') === $user['id'] ? $errors->first('email') : null"/>
                    <x-select name="role" id="edit-role-{{ $user['id'] }}" label="Peran" :error="old('_form') === 'edit' && (int) old('user_id') === $user['id'] ? $errors->first('role') : null">
                        @foreach (['Owner', 'Agronomis', 'Petani Lapangan'] as $role)<option value="{{ $role }}" @selected((old('_form') === 'edit' && (int) old('user_id') === $user['id'] ? old('role') : $user['role']) === $role)>{{ $role }}</option>@endforeach
                    </x-select>
                    <div class="flex justify-end gap-2 border-t border-berry-border pt-4">
                        <x-button variant="secondary" @click="$dispatch('open-modal-close', 'edit-user-{{ $user['id'] }}')">Batal</x-button>
                        <x-button type="submit">Simpan Perubahan</x-button>
                    </div>
                </form>
            </x-modal>
        @endforeach
    </div>
@endsection
