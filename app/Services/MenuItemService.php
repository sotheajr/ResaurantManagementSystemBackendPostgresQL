<?php

namespace App\Services;

use App\Repositories\Contracts\MenuItemRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Http\UploadedFile;

class MenuItemService
{
    use UploadImageTrait;

    protected $menuItemRepository;

    public function __construct(MenuItemRepositoryInterface $menuItemRepository)
    {
        $this->menuItemRepository = $menuItemRepository;
    }

    public function getAllItems()
    {
        return $this->menuItemRepository->getAllWithCategory();
    }

    public function getItemById(int $id)
    {
        return $this->menuItemRepository->findByIdWithCategory($id);
    }

    public function getAvailableItems()
    {
        return $this->menuItemRepository->getAvailableItems();
    }

    public function createItem(array $data, ?UploadedFile $imageFile = null)
    {
        if ($imageFile) {
            $data['image'] = $this->uploadImage($imageFile, 'menu');
        }

        return $this->menuItemRepository->createItem($data);
    }

    public function updateItem(int $id, array $data, ?UploadedFile $imageFile = null)
    {
        $item = $this->menuItemRepository->findByIdWithCategory($id);

        if ($imageFile) {
            if ($item->image) {
                $this->deleteImage($item->image);
            }
            $data['image'] = $this->uploadImage($imageFile, 'menu');
        }

        return $this->menuItemRepository->updateItem($id, $data);
    }

    public function deleteItem(int $id)
    {
        $item = $this->menuItemRepository->findByIdWithCategory($id);

        if ($item->image) {
            $this->deleteImage($item->image);
        }

        return $this->menuItemRepository->deleteItem($id);
    }
}
