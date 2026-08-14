<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use App\Models\RolePermission;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RoleRepository implements RoleRepositoryInterface
{
    public function getAllWithPermissions()
    {
        return Role::with('permissions')->get();
    }

    public function findByIdWithPermissions(int $id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function createRole(array $data)
    {
        return Role::create($data);
    }

    public function updateRole(int $id, array $data)
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role;
    }

    public function deleteRoleWithCleanup(int $id)
    {
        return DB::transaction(function () use ($id) {
            // 1. Reassign users to Cashier (3)
            User::where('role_id', $id)->update(['role_id' => 3]);

            // 2. Delete related permissions
            RolePermission::where('role_id', $id)->delete();

            // 3. Delete the role
            return Role::findOrFail($id)->delete();
        });
    }

    public function syncRolePermissions(int $roleId, array $permissionsList)
    {
        if ($roleId === 1) {
            throw new \Exception("Cannot modify or revoke Admin permissions.", 403);
        }
        return DB::transaction(function () use ($roleId, $permissionsList) {
            foreach ($permissionsList as $permission) {
                RolePermission::updateOrCreate(
                    ['role_id' => $roleId, 'permission_name' => $permission['permission_name']],
                    ['can_access' => $permission['can_access']]
                );
            }
            return $this->findByIdWithPermissions($roleId);
        });
    }
}
