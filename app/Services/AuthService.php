<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\RolePermission;
use App\Traits\UploadImageTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class AuthService
{
    use UploadImageTrait;

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['role_id'] = $data['role_id'] ?? 2;
        $data['status'] = 'Active';

        $user = $this->userRepository->create($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => [
                'id' => $user->user_id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'role' => $user->role->role_name ?? 'Waiter',
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'status' => $user->status,
            ],
            'token' => $token
        ];
    }

    public function login(array $credentials)
    {
        $user = $this->userRepository->findByUsername($credentials['username']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'Active') {
            throw ValidationException::withMessages([
                'username' => ['Account is inactive.'],
            ]);
        }

        $permissions = RolePermission::where('role_id', $user->role_id)
            ->where('can_access', true)
            ->pluck('permission_name')
            ->toArray();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => [
                'id' => $user->user_id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'role' => $user->role->role_name ?? 'Waiter',
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
            ],
            'permissions' => $permissions,
            'token' => $token
        ];
    }

    public function getProfile($user)
    {
        [$user, $permissions] = $this->userRepository->getProfileWithPermissions($user->user_id);
        $image = $user->image;

        if ($image && !str_starts_with($image, 'http')) {
            $image = 'storage/' . $image;
        }

        return [
            'user' => [
                'id' => $user->user_id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'role' => $user->role->role_name ?? 'Waiter',
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'salary' => $user->salary,
                'hire_date' => $user->hire_date,
                'status' => $user->status,
                'image' => $image,
            ],
            'permissions' => $permissions,
        ];
    }

    public function updateProfileImage($user, UploadedFile $file)
    {
        if ($user->image) {
            $this->deleteImage($user->image);
        }

        $path = $this->uploadImage($file, 'users');
        return $this->userRepository->updateImage($user->user_id, $path);
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
    }
}
