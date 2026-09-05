<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableId = $this->route('id');

        return [
            'table_number' => [
                'sometimes',
                'required',
                'integer',
                Rule::unique('tables', 'table_number')->ignore($tableId),
            ],
            'capacity' => 'sometimes|required|integer|min:1',
            'location' => 'sometimes|required|string|max:100',
            'status' => 'nullable|string|in:Available,Occupied,Reserved',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
