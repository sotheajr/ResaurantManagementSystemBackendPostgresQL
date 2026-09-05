<?php

namespace App\Repositories\Eloquent;

use App\Models\MenuItem;
use App\Repositories\Contracts\MenuItemRepositoryInterface;

class MenuItemRepository implements MenuItemRepositoryInterface
{
    public function getAllWithCategory()
    {
        return MenuItem::with('category')->orderBy('id', 'asc')->get();
    }

    public function findByIdWithCategory(int $id)
    {
        return MenuItem::with('category')->findOrFail($id);
    }

    public function getAvailableItems()
    {
        return MenuItem::with('category')->where('status', 'Available')->orderBy('id', 'asc')->get();
    }

    public function createItem(array $data)
    {
        return MenuItem::create($data);
    }

    public function updateItem(int $id, array $data)
    {
        $item = MenuItem::findOrFail($id);
        $item->update($data);
        return $item->fresh();
    }

    public function deleteItem(int $id)
    {
        $item = MenuItem::findOrFail($id);
        return $item->delete();
    }
}
