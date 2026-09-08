<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_id' => 'nullable|exists:tables,id',
            'customer_id' => 'nullable|exists:customers,id',
            'user_id' => 'nullable|exists:users,user_id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.special_instructions' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required for the order.',
            'items.min' => 'At least one item is required for the order.',
            'items.*.menu_item_id.required' => 'Menu item is required for each order item.',
            'items.*.menu_item_id.exists' => 'The selected menu item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each order item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.price.required' => 'Price is required for each order item.',
            'items.*.price.min' => 'Price must be at least 0.',
        ];
    }
}
