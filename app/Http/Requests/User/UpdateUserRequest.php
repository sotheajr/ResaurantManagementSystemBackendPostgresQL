<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');
        return [
            'username' => ['sometimes', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId, 'user_id')],
            'password' => 'sometimes|string|min:6',
            'full_name' => 'sometimes|string|max:100',
            'role_id' => 'sometimes|integer|exists:roles,role_id',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId, 'user_id')],
            'gender' => 'nullable|in:Male,Female,Other',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'nullable|string|in:Active,Inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
