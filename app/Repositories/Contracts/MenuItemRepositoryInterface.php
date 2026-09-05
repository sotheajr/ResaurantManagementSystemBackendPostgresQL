<?php

namespace App\Repositories\Contracts;

interface MenuItemRepositoryInterface
{
    public function getAllWithCategory();
    public function findByIdWithCategory(int $id);
    public function getAvailableItems();
    public function createItem(array $data);
    public function updateItem(int $id, array $data);
    public function deleteItem(int $id);
}
