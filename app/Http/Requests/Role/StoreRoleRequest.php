<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_name' => 'required|string|max:30|unique:roles,role_name',
            'role_name_kh' => 'nullable|string|max:255',
        ];
    }
}
