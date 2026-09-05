<?php

namespace App\Repositories\Contracts;

interface InventoryRepositoryInterface
{
    public function getAll();
    public function findLowStock();
    public function findById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function updateStock(int $id, $quantity);
    public function delete(int $id);
}