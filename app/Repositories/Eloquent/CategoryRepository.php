<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Models\SubCategory;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAllWithSubCategories()
    {
        return Category::with('subCategories')->orderBy('id', 'asc')->get();
    }

    public function findByIdWithSubCategories(int $id)
    {
        return Category::with('subCategories')->findOrFail($id);
    }

    public function getSubCategories(int $categoryId)
    {
        $category = Category::with('subCategories')->findOrFail($categoryId);
        return $category->subCategories;
    }

    public function createCategory(array $data)
    {
        $subCategories = $data['sub_categories'] ?? null;
        unset($data['sub_categories']);

        $category = Category::create($data);

        if ($subCategories) {
            $parsed = is_string($subCategories) ? json_decode($subCategories, true) : $subCategories;
            if (is_array($parsed)) {
                foreach ($parsed as $subName) {
                    if (is_string($subName) && trim($subName) !== '') {
                        SubCategory::create([
                            'category_id' => $category->id,
                            'sub_category_name' => trim($subName),
                        ]);
                    } elseif (is_array($subName) && isset($subName['sub_category_name'])) {
                        SubCategory::create([
                            'category_id' => $category->id,
                            'sub_category_name' => trim($subName['sub_category_name']),
                            'description' => $subName['description'] ?? null,
                        ]);
                    }
                }
            }
        }

        return $this->findByIdWithSubCategories($category->id);
    }

    public function updateCategory(int $id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $this->findByIdWithSubCategories($category->id);
    }

    public function deleteCategory(int $id)
    {
        $category = Category::findOrFail($id);
        SubCategory::where('category_id', $id)->delete();
        return $category->delete();
    }
}

