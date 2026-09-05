<?php

namespace App\Repositories\Eloquent;

use App\Models\SubCategory;
use App\Repositories\Contracts\SubCategoryRepositoryInterface;

class SubCategoryRepository implements SubCategoryRepositoryInterface
{
    public function getAll()
    {
        return SubCategory::with('category')->orderBy('sub_category_id', 'asc')->get();
    }

    public function findById(int $id)
    {
        return SubCategory::with('category')->findOrFail($id);
    }

    public function findByCategoryId(int $categoryId)
    {
        return SubCategory::where('category_id', $categoryId)->orderBy('sub_category_id', 'asc')->get();
    }

    public function create(array $data)
    {
        return SubCategory::create($data);
    }

    public function update(int $id, array $data)
    {
        $subCategory = SubCategory::findOrFail($id);
        $subCategory->update($data);
        return $subCategory->fresh();
    }

    public function delete(int $id)
    {
        $subCategory = SubCategory::findOrFail($id);
        return $subCategory->delete();
    }
}
