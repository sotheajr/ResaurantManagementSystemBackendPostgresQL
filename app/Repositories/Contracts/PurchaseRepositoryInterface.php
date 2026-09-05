<?php

namespace App\Repositories\Contracts;

interface PurchaseRepositoryInterface
{
    public function getAll();
    public function findById(int $id);
    public function create(array $data, array $items);
    public function update(int $id, array $data, array $items);
    public function delete(int $id);
}