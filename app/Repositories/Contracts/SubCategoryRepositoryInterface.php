<?php

namespace App\Repositories\Contracts;

interface SubCategoryRepositoryInterface
{
    public function getAll();
    public function findById(int $id);
    public function findByCategoryId(int $categoryId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
