<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'purchase_date' => 'nullable|date',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|exists:inventory,inventory_id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'invoice_attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one purchase item is required.',
            'items.*.inventory_id.exists' => 'The selected inventory item does not exist.',
        ];
    }
}
