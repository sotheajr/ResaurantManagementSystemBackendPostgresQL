<?php

namespace App\Services;

use App\Repositories\Contracts\SubCategoryRepositoryInterface;

class SubCategoryService
{
    protected $subCategoryRepository;

    public function __construct(SubCategoryRepositoryInterface $subCategoryRepository)
    {
        $this->subCategoryRepository = $subCategoryRepository;
    }

    public function getAllSubCategories()
    {
        return $this->subCategoryRepository->getAll();
    }

    public function getSubCategoryById(int $id)
    {
        return $this->subCategoryRepository->findById($id);
    }

    public function getSubCategoriesByCategoryId(int $categoryId)
    {
        return $this->subCategoryRepository->findByCategoryId($categoryId);
    }

    public function createSubCategory(array $data)
    {
        return $this->subCategoryRepository->create($data);
    }

    public function updateSubCategory(int $id, array $data)
    {
        return $this->subCategoryRepository->update($id, $data);
    }

    public function deleteSubCategory(int $id)
    {
        return $this->subCategoryRepository->delete($id);
    }
}
