<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\RolePermission;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function create(array $data)
    {
        $user = User::create($data);
        return $user->load('role');
    }

    public function findByUsername(string $username)
    {
        return User::where('username', $username)->with('role')->first();
    }

    public function getProfileWithPermissions(int $userId)
    {
        $user = User::with('role')->findOrFail($userId);
        $permissions = RolePermission::where('role_id', $user->role_id)
            ->where('can_access', true)
            ->pluck('permission_name')
            ->toArray();
        
        return [$user, $permissions];
    }

    public function updateImage(int $userId, string $imagePath)
    {
        $user = User::findOrFail($userId);
        $user->update(['image' => $imagePath]);
        return $user;
    }

    public function getAllWithRoles()
    {
        return User::with('role')->orderBy('user_id', 'desc')->get();
    }

    public function findByIdWithRole(int $id)
    {
        return User::with('role')->findOrFail($id);
    }

    public function createUser(array $data)
    {
        return User::create($data);
    }

    public function updateUser(int $id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function deleteUser(int $id)
    {
        return User::findOrFail($id)->delete();
    }
}
