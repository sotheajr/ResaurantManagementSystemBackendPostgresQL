<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Exception;

class UserService
{
    use UploadImageTrait;

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAllWithRoles();
    }

    public function getUser(int $id)
    {
        return $this->userRepository->findByIdWithRole($id);
    }

    public function createUser(array $data, ?UploadedFile $image)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($image) {
            $data['image'] = $this->uploadImage($image, 'users');
        }

        return $this->userRepository->createUser($data);
    }

    public function updateUser(int $id, array $data, ?UploadedFile $image)
    {
        $user = $this->userRepository->findByIdWithRole($id);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($image) {
            if ($user->image) {
                $this->deleteImage($user->image);
            }
            $data['image'] = $this->uploadImage($image, 'users');
        }

        return $this->userRepository->updateUser($id, $data);
    }

    public function deleteUser(int $id)
    {
        // Prevent deletion of admin user
        $user = $this->userRepository->findByIdWithRole($id);
        if ($user->user_id == 1 || $user->username === 'admin') {
            throw new Exception("Cannot delete the main admin account.");
        }

        if ($user->image) {
            $this->deleteImage($user->image);
        }

        return $this->userRepository->deleteUser($id);
    }
}
