<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');
        return [
            'role_name' => ['required', 'string', 'max:30', Rule::unique('roles', 'role_name')->ignore($roleId, 'role_id')],
            'role_name_kh' => 'nullable|string|max:255',
        ];
    }
}
