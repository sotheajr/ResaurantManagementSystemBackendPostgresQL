<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function create(array $data);
    public function findByUsername(string $username);
    public function getProfileWithPermissions(int $userId);
    public function updateImage(int $userId, string $imagePath);
    
    // User CRUD methods
    public function getAllWithRoles();
    public function findByIdWithRole(int $id);
    public function createUser(array $data);
    public function updateUser(int $id, array $data);
    public function deleteUser(int $id);
}
