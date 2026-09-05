<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_number' => 'required|integer|unique:tables,table_number',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string|max:100',
            'status' => 'nullable|string|in:Available,Occupied,Reserved',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
