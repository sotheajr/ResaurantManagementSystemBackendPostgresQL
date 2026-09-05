<?php

namespace App\Repositories\Contracts;

interface TableRepositoryInterface
{
    public function getAll();
    public function findById(int $id);
    public function getAvailableTables();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
