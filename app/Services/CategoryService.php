<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Http\UploadedFile;

class CategoryService
{
    use UploadImageTrait;

    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories()
    {
        return $this->categoryRepository->getAllWithSubCategories();
    }

    public function getCategoryById(int $id)
    {
        return $this->categoryRepository->findByIdWithSubCategories($id);
    }

    public function getSubCategoriesByCategoryId(int $id)
    {
        return $this->categoryRepository->getSubCategories($id);
    }

    public function createCategory(array $data, ?UploadedFile $imageFile = null)
    {
        if ($imageFile) {
            $data['image'] = $this->uploadImage($imageFile, 'categories');
        }

        return $this->categoryRepository->createCategory($data);
    }

    public function updateCategory(int $id, array $data, ?UploadedFile $imageFile = null)
    {
        $category = $this->categoryRepository->findByIdWithSubCategories($id);

        if ($imageFile) {
            if ($category->image) {
                $this->deleteImage($category->image);
            }
            $data['image'] = $this->uploadImage($imageFile, 'categories');
        }

        return $this->categoryRepository->updateCategory($id, $data);
    }

    public function deleteCategory(int $id)
    {
        $category = $this->categoryRepository->findByIdWithSubCategories($id);

        if ($category->image) {
            $this->deleteImage($category->image);
        }

        return $this->categoryRepository->deleteCategory($id);
    }
}
