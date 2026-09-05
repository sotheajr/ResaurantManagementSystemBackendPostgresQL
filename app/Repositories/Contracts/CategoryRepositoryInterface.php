<?php

namespace App\Repositories\Contracts;

interface CategoryRepositoryInterface
{
    public function getAllWithSubCategories();
    public function findByIdWithSubCategories(int $id);
    public function getSubCategories(int $categoryId);
    public function createCategory(array $data);
    public function updateCategory(int $id, array $data);
    public function deleteCategory(int $id);
}
