<?php

namespace App\Repositories\Eloquent;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepositoryInterface;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function getAll()
    {
        return Inventory::orderBy('ingredient_name', 'asc')->get();
    }

    public function findLowStock()
    {
        return Inventory::lowStock()->get();
    }

    public function findById(int $id)
    {
        return Inventory::findOrFail($id);
    }

    public function create(array $data)
    {
        return Inventory::create($data);
    }

    public function update(int $id, array $data)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->update($data);
        return $inventory->fresh();
    }

    public function updateStock(int $id, $quantity)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->increment('quantity', $quantity);
        return $inventory->fresh();
    }

    public function delete(int $id)
    {
        $inventory = Inventory::findOrFail($id);
        return $inventory->delete();
    }
}