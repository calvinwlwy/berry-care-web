<?php

namespace App\Http\Controllers;

use App\Services\DummyDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagementController extends Controller
{
    public function __construct(private readonly DummyDataService $data) {}

    public function polybags(Request $request): View
    {
        $query = (string) $request->query('q', '');
        $block = (string) $request->query('block', 'Semua Blok');
        $polybags = array_values(array_filter($this->data->polybags(), function (array $polybag) use ($query, $block): bool {
            return ($block === 'Semua Blok' || $polybag['block'] === $block)
                && ($query === '' || str_contains(strtolower($polybag['id']), strtolower($query)));
        }));

        return view('pages.polybag', $this->layoutData('Lahan & Polybag') + [
            'polybags' => $this->paginate($polybags, $request),
            'query' => $query,
            'search' => $query,
            'block' => $block,
            'totalSensors' => 120,
            'onlineSensors' => 118,
        ]);
    }

    public function diagnosis(Request $request): View
    {
        $query = (string) $request->query('q', '');
        $filter = (string) $request->query('filter', 'Semua');
        $scans = array_values(array_filter($this->data->scans(), function (array $scan) use ($query, $filter): bool {
            $matchesFilter = match ($filter) {
                'Daun' => $scan['type'] === 'Daun',
                'Buah' => $scan['type'] === 'Buah',
                'Jamur' => $scan['danger'],
                default => true,
            };

            return $matchesFilter
                && ($query === '' || str_contains(strtolower($scan['label'].' '.$scan['pot']), strtolower($query)));
        }));

        return view('pages.diagnosis', $this->layoutData('Riwayat Diagnosis AI') + [
            'scans' => $this->paginate($scans, $request, 6),
            'query' => $query,
            'filter' => $filter,
            'today' => Carbon::now()->locale('id')->translatedFormat('j M Y'),
            'scanCounts' => [
                'Semua' => 38,
                'Daun' => 26,
                'Buah' => 12,
                'Jamur' => 5,
            ],
            'polybagIds' => array_column($this->data->polybags(), 'id'),
        ]);
    }

    public function uploadScan(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'polybag_id' => ['required', Rule::in(array_column($this->data->polybags(), 'id'))],
        ], [
            'photo.required' => 'Pilih foto daun atau buah terlebih dahulu.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG atau PNG.',
            'photo.max' => 'Ukuran foto maksimal 10 MB.',
            'polybag_id.required' => 'Pilih ID polybag.',
            'polybag_id.in' => 'ID polybag tidak valid.',
        ]);

        return redirect()->route('diagnosis.index')->with('status', 'Foto diterima untuk simulasi diagnosis.');
    }

    public function alerts(): View
    {
        $alerts = array_map(static function (array $alert): array {
            $alert['done'] = false;

            return $alert;
        }, $this->data->alerts());

        return view('pages.alerts', $this->layoutData('Early Warning & Alerts') + [
            'alerts' => $alerts,
            'openCount' => $this->data->activeAlertCount(),
        ]);
    }

    public function thresholds(Request $request): View
    {
        return view('pages.thresholds', $this->layoutData('Konfigurasi Threshold') + [
            'values' => $request->session()->get('dummy.thresholds', $this->data->thresholds()),
        ]);
    }

    public function saveThresholds(Request $request): RedirectResponse
    {
        $values = $request->validate([
            'humidity' => ['required', 'numeric', 'between:0,100'],
            'water' => ['required', 'numeric', 'between:0,100'],
            'n' => ['required', 'numeric', 'min:0', 'max:10000'],
            'p' => ['required', 'numeric', 'min:0', 'max:10000'],
            'k' => ['required', 'numeric', 'min:0', 'max:10000'],
        ], [
            '*.required' => 'Nilai ambang wajib diisi.',
            '*.numeric' => 'Nilai ambang harus berupa angka.',
            '*.between' => 'Nilai harus berada antara :min dan :max.',
            '*.min' => 'Nilai minimum adalah :min.',
            '*.max' => 'Nilai maksimum adalah :max.',
        ]);

        $request->session()->put('dummy.thresholds', $values);

        return back()->with('status', 'Pengaturan berhasil diperbarui.');
    }

    public function users(Request $request): View
    {
        $users = $request->session()->get('dummy.users', $this->data->users());
        $users = array_map(static function (array $user, int $index): array {
            $user['id'] = $index;
            $user['initials'] = collect(explode(' ', $user['name']))
                ->map(static fn (string $part): string => mb_substr($part, 0, 1))
                ->take(2)
                ->join('');

            return $user;
        }, $users, array_keys($users));
        $query = (string) $request->query('q', '');
        $filtered = array_values(array_filter($users, static function (array $user) use ($query): bool {
            return $query === '' || str_contains(strtolower($user['name'].$user['email'].$user['role']), strtolower($query));
        }));

        return view('pages.users', $this->layoutData('Manajemen Pengguna') + [
            'users' => $this->paginate($filtered, $request, 10),
            'totalUsers' => count($users),
            'query' => $query,
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $values = $this->validateUser($request);
        $users = $request->session()->get('dummy.users', $this->data->users());
        $users[] = $values + ['last' => 'Belum pernah'];
        $request->session()->put('dummy.users', $users);

        return redirect()->route('users.index')->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    public function updateUser(Request $request, int $user): RedirectResponse
    {
        $users = $request->session()->get('dummy.users', $this->data->users());
        abort_unless(array_key_exists($user, $users), 404);
        $values = $this->validateUser($request, $users[$user]['email']);
        $users[$user] = array_merge($users[$user], $values);
        $request->session()->put('dummy.users', $users);

        return redirect()->route('users.index')->with('status', 'Data pengguna berhasil diperbarui.');
    }

    private function validateUser(Request $request, ?string $currentEmail = null): array
    {
        return Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($currentEmail, 'email')],
            'role' => ['required', Rule::in(['Owner', 'Agronomis', 'Petani Lapangan'])],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Email sudah digunakan.',
            'role.required' => 'Pilih peran pengguna.',
            'role.in' => 'Peran yang dipilih tidak valid.',
        ])->validate();
    }

    private function layoutData(string $title): array
    {
        return [
            'title' => $title,
            'breadcrumb' => $title,
            'search' => '',
            'weather' => ['location' => 'Lembang', 'temperature' => 21, 'humidity' => 84, 'condition' => 'Lembap Basah'],
            'activeAlerts' => $this->data->activeAlertCount(),
            'systemStatus' => 'Sistem beroperasi normal',
            'lastSync' => '10:54 WIB',
        ];
    }

    private function paginate(array $items, Request $request, int $perPage = 5): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $slice = array_slice($items, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator(
            $slice,
            count($items),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );
    }
}
