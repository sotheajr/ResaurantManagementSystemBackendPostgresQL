<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ingredient_name' => 'sometimes|required|string|max:100',
            'quantity' => 'nullable|numeric|min:0|decimal:0,2',
            'unit' => 'nullable|string|max:20',
            'minimum_stock' => 'nullable|numeric|min:0|decimal:0,2',
        ];
    }
}