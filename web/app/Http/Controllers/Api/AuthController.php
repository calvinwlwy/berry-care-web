<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\BcryptPasswordByteLimit;
use App\Services\LoginAttemptLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        if (! config('services.api.registration_enabled')) {
            return response()->json([
                'message' => 'Registrasi API dinonaktifkan.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', new BcryptPasswordByteLimit, 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 255 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'petani',
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $this->userData($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $loginLogger = app(LoginAttemptLogger::class);

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.max' => 'Password maksimal 255 karakter.',
        ]);

        if ($validator->fails()) {
            $loginLogger->failed($request, $request->input('email'), 'invalid_input');

            return $this->validationError($validator->errors()->toArray());
        }

        if (! Auth::once($validator->validated())) {
            $loginLogger->failed($request, $request->input('email'), 'invalid_credentials');

            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $this->userData($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userData($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    public function getAllUser(): JsonResponse
    {
        return response()->json([
            'message' => 'Daftar pengguna berhasil diambil.',
            'data' => User::query()
                ->orderBy('id')
                ->get()
                ->map(fn (User $user): array => $this->userResource($user)),
        ]);
    }

    public function addUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', new BcryptPasswordByteLimit],
        ], $this->userValidationMessages());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $user = User::create([
            ...$validator->validated(),
            'role' => 'petani',
        ]);

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => $this->userResource($user),
        ], 201);
    }

    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'max:255', new BcryptPasswordByteLimit],
        ], $this->userValidationMessages());

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $user->fill($validator->validated());
        $user->save();

        return response()->json([
            'message' => 'Pengguna berhasil diperbarui.',
            'data' => $this->userResource($user->fresh()),
        ]);
    }

    public function deleteUser(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        if ($user->is($request->user())) {
            return response()->json([
                'message' => 'Akun admin yang sedang digunakan tidak dapat dihapus.',
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'message' => 'Data yang diberikan tidak valid.',
            'errors' => $errors,
        ], 422);
    }

    /**
     * @return array<string, string>
     */
    private function userValidationMessages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 255 karakter.',
        ];
    }

    /**
     * @return array{id: int, name: string, email: string, role: string}
     */
    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }

    /**
     * @return array{id: int, name: string, email: string, role: string, created_at: ?string}
     */
    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
