<?php

namespace App\Services;

use App\Repositories\Contracts\InventoryRepositoryInterface;

class InventoryService
{
    protected $inventoryRepository;

    public function __construct(InventoryRepositoryInterface $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    public function getAllInventory()
    {
        return $this->inventoryRepository->getAll();
    }

    public function getLowStockItems()
    {
        return $this->inventoryRepository->findLowStock();
    }

    public function getInventoryById(int $id)
    {
        return $this->inventoryRepository->findById($id);
    }

    public function createInventory(array $data)
    {
        return $this->inventoryRepository->create($data);
    }

    public function updateInventory(int $id, array $data)
    {
        return $this->inventoryRepository->update($id, $data);
    }

    public function updateStock(int $id, $quantity)
    {
        return $this->inventoryRepository->updateStock($id, $quantity);
    }

    public function deleteInventory(int $id)
    {
        return $this->inventoryRepository->delete($id);
    }
}