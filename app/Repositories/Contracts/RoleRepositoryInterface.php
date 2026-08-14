<?php

namespace App\Repositories\Contracts;

interface RoleRepositoryInterface
{
    public function getAllWithPermissions();
    public function findByIdWithPermissions(int $id);
    public function createRole(array $data);
    public function updateRole(int $id, array $data);
    public function deleteRoleWithCleanup(int $id);
    public function syncRolePermissions(int $roleId, array $permissionsList);
}
