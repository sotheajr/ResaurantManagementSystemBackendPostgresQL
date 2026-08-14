<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Profile\UpdateProfileImageRequest;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $data = $this->authService->register($request->validated());
        return $this->successResponse($data, 'User registered successfully', 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request->validated());
        return $this->successResponse($data, 'Login successful', 200);
    }

    public function profile(Request $request)
    {
        $data = $this->authService->getProfile($request->user());
        return $this->successResponse($data);
    }

    public function updateImage(UpdateProfileImageRequest $request)
    {
        $user = $this->authService->updateProfileImage($request->user(), $request->file('image'));
        return $this->successResponse(['image' => 'storage/' . $user->image], 'Profile image updated successfully');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->successResponse(null, 'Logged out successfully');
    }
}
