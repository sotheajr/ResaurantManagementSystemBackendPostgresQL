<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategory\StoreSubCategoryRequest;
use App\Http\Requests\SubCategory\UpdateSubCategoryRequest;
use App\Services\SubCategoryService;
use App\Traits\ApiResponseTrait;

class SubCategoryController extends Controller
{
    use ApiResponseTrait;

    protected $subCategoryService;

    public function __construct(SubCategoryService $subCategoryService)
    {
        $this->subCategoryService = $subCategoryService;
    }

    public function index()
    {
        try {
            $subCategories = $this->subCategoryService->getAllSubCategories();
            return $this->successResponse($subCategories);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $subCategory = $this->subCategoryService->getSubCategoryById($id);
            return $this->successResponse($subCategory);
        } catch (\Exception $e) {
            return $this->errorResponse('Sub-category not found', 404);
        }
    }

    public function store(StoreSubCategoryRequest $request)
    {
        try {
            $subCategory = $this->subCategoryService->createSubCategory($request->validated());
            return $this->successResponse($subCategory, 'Sub-category created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update(UpdateSubCategoryRequest $request, int $id)
    {
        try {
            $subCategory = $this->subCategoryService->updateSubCategory($id, $request->validated());
            return $this->successResponse($subCategory, 'Sub-category updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->subCategoryService->deleteSubCategory($id);
            return $this->successResponse(null, 'Sub-category deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Sub-category not found', 404);
        }
    }
}
