<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Requests\Role\UpdateRolePermissionsRequest;
use App\Services\RoleService;
use App\Traits\ApiResponseTrait;

class RoleController extends Controller
{
    use ApiResponseTrait;

    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        try {
            return $this->successResponse($this->roleService->getAllRoles());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreRoleRequest $request)
    {
        try {
            $role = $this->roleService->createRole($request->validated());
            return $this->successResponse($role, 'Role created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            return $this->successResponse($this->roleService->getRoleById($id));
        } catch (\Exception $e) {
            return $this->errorResponse('Role not found', 404);
        }
    }

    public function update(UpdateRoleRequest $request, int $id)
    {
        try {
            $role = $this->roleService->updateRole($id, $request->validated());
            return $this->successResponse($role, 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->roleService->deleteRole($id);
            return $this->successResponse(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function updatePermissions(UpdateRolePermissionsRequest $request, int $id)
    {
        try {
            $role = $this->roleService->updatePermissions($id, $request->validated()['permissions']);
            return $this->successResponse($role, 'Permissions updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
