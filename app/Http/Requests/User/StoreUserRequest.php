<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'full_name' => 'required|string|max:100',
            'role_id' => 'required|integer|exists:roles,role_id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:users,email',
            'gender' => 'nullable|in:Male,Female,Other',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
            'status' => 'nullable|string|in:Active,Inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
