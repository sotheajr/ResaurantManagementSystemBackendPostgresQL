<?php

namespace App\Services;

use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Http\UploadedFile;

class InventoryService
{
    use UploadImageTrait;

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

    public function createInventory(array $data, ?UploadedFile $imageFile = null)
    {
        if ($imageFile) {
            $data['image'] = $this->uploadImage($imageFile, 'inventory');
        }

        return $this->inventoryRepository->create($data);
    }

    public function updateInventory(int $id, array $data, ?UploadedFile $imageFile = null)
    {
        $item = $this->inventoryRepository->findById($id);

        if ($imageFile) {
            if ($item->image) {
                $this->deleteImage($item->image);
            }
            $data['image'] = $this->uploadImage($imageFile, 'inventory');
        }

        return $this->inventoryRepository->update($id, $data);
    }

    public function updateStock(int $id, $quantity)
    {
        return $this->inventoryRepository->updateStock($id, $quantity);
    }

    public function deleteInventory(int $id)
    {
        $item = $this->inventoryRepository->findById($id);

        if ($item->image) {
            $this->deleteImage($item->image);
        }

        return $this->inventoryRepository->delete($id);
    }
}
