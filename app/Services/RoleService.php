<?php

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Exception;

class RoleService
{
    protected $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function getAllRoles()
    {
        return $this->roleRepository->getAllWithPermissions();
    }

    public function getRoleById(int $id)
    {
        return $this->roleRepository->findByIdWithPermissions($id);
    }

    public function createRole(array $data)
    {
        return $this->roleRepository->createRole($data);
    }

    public function updateRole(int $id, array $data)
    {
        return $this->roleRepository->updateRole($id, $data);
    }

    public function deleteRole(int $id)
    {
        if ($id <= 3) {
            throw new Exception("Cannot delete default roles", 400);
        }
        return $this->roleRepository->deleteRoleWithCleanup($id);
    }

    public function updatePermissions(int $roleId, array $permissions)
    {
        if ($roleId === 1) {
            throw new \Exception("Cannot modify or revoke Admin permissions.", 403);
        }
        return $this->roleRepository->syncRolePermissions($roleId, $permissions);
    }
}
