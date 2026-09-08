<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface
{
    public function getAll(string $type = 'history', array $filters = []);

    public function findById(int $id);

    public function create(array $data, array $items);

    public function update(int $id, array $data);

    public function updateStatus(int $id, string $status);

    public function delete(int $id);
}
