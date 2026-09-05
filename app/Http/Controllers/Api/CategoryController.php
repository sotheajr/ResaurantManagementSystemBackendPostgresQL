<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Services\CategoryService;
use App\Traits\ApiResponseTrait;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        try {
            $categories = $this->categoryService->getAllCategories();
            return $this->successResponse($categories);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $imageFile = $request->file('image');
            $category = $this->categoryService->createCategory($request->validated(), $imageFile);
            return $this->successResponse($category, 'Category created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);
            return $this->successResponse($category);
        } catch (\Exception $e) {
            return $this->errorResponse('Category not found', 404);
        }
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        try {
            $imageFile = $request->file('image');
            $category = $this->categoryService->updateCategory($id, $request->validated(), $imageFile);
            return $this->successResponse($category, 'Category updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->categoryService->deleteCategory($id);
            return $this->successResponse(null, 'Category deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Category not found', 404);
        }
    }

    public function subCategories(int $id)
    {
        try {
            $subCategories = $this->categoryService->getSubCategoriesByCategoryId($id);
            return $this->successResponse($subCategories);
        } catch (\Exception $e) {
            return $this->errorResponse('Category not found', 404);
        }
    }
}

