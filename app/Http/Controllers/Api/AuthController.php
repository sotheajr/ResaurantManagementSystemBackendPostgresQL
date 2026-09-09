<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Profile\UpdateProfileImageRequest;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
        $file = $request->file('image') ?? $request->file('avatar') ?? $request->file('profile_image');
        $user = $request->user();

        if (!$file) {
            return $this->errorResponse('Profile image file is required.', 422);
        }

        $uploadedUser = $this->authService->updateProfileImage($user, $file);

        $imageUrl = $uploadedUser->image ?? $uploadedUser->avatar ?? $uploadedUser->profile_image ?? null;
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = 'storage/' . $imageUrl;
        }

        $user->refresh();
        if (Schema::hasColumn('users', 'avatar')) {
            $user->avatar = $imageUrl;
        }
        if (Schema::hasColumn('users', 'profile_image')) {
            $user->profile_image = $imageUrl;
        }
        $user->image = $imageUrl;
        $user->save();

        $payload = [
            'avatar' => $imageUrl,
            'avatar_url' => $imageUrl,
            'image' => $imageUrl,
            'user' => $user->fresh(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully',
            'data' => $payload,
            'avatar' => $imageUrl,
            'avatar_url' => $imageUrl,
            'image' => $imageUrl,
            'user' => $user->fresh(),
        ], 200);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->successResponse(null, 'Logged out successfully');
    }
}
