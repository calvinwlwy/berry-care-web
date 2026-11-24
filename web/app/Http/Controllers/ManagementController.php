<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\IndexUsersRequest;
use App\Http\Requests\Web\StoreUserRequest;
use App\Http\Requests\Web\UpdateUserRequest;
use App\Models\User;
use App\Services\DummyDataService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        ]);
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

    public function users(IndexUsersRequest $request): View
    {
        $filters = $request->validated();
        $query = User::query()->orderBy('name')->orderBy('id');

        if (! empty($filters['q'])) {
            $search = $filters['q'];
            $query->where(static function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }
        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return view('pages.users', $this->layoutData('Manajemen Pengguna') + [
            'users' => $query->paginate(10)->withQueryString(),
            'totalUsers' => User::query()->count(),
            'query' => $filters['q'] ?? '',
            'role' => $filters['role'] ?? '',
        ]);
    }

    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create($request->safe()->only(['name', 'email', 'password', 'role']));

        return redirect()->route('users.index')->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $values = $request->safe()->only(['name', 'email', 'role', 'password']);
        $result = DB::transaction(function () use ($request, $user, $values): ?string {
            $adminIds = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($request->user()->is($lockedUser) && $values['role'] !== 'admin') {
                return 'Anda tidak dapat menurunkan peran akun admin yang sedang digunakan.';
            }

            if ($lockedUser->role === 'admin' && $values['role'] !== 'admin' && $adminIds->count() <= 1) {
                return 'Peran admin terakhir tidak dapat diubah.';
            }

            $passwordChanged = isset($values['password'])
                && ! Hash::check($values['password'], $lockedUser->password);
            if (! $passwordChanged) {
                unset($values['password']);
            }
            $roleChanged = $lockedUser->role !== $values['role'];

            $lockedUser->fill($values)->save();
            if ($passwordChanged || $roleChanged) {
                $lockedUser->tokens()->delete();
            }

            return null;
        });

        if ($result !== null) {
            return back()
                ->withErrors(['role' => $result])
                ->withInput($request->only(['name', 'email', 'role', '_form', 'user_id']));
        }

        return redirect()->route('users.index')->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        $result = DB::transaction(function () use ($request, $user): ?string {
            $adminIds = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($request->user()->is($lockedUser)) {
                return 'Anda tidak dapat menghapus akun yang sedang digunakan.';
            }
            if ($lockedUser->role === 'admin' && $adminIds->count() <= 1) {
                return 'Admin terakhir tidak dapat dihapus.';
            }

            $lockedUser->tokens()->delete();
            $lockedUser->delete();

            return null;
        });

        if ($result !== null) {
            return back()
                ->withErrors(['delete' => $result])
                ->withInput(['_form' => 'delete', 'user_id' => $user->id]);
        }

        return redirect()->route('users.index')->with('status', 'Pengguna berhasil dihapus.');
    }

    private function layoutData(string $title): array
    {
        return [
            'title' => $title,
            'breadcrumb' => $title,
            'search' => '',
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
