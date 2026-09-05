<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Services\SupplierService;
use App\Traits\ApiResponseTrait;

class SupplierController extends Controller
{
    use ApiResponseTrait;

    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index()
    {
        try {
            $suppliers = $this->supplierService->getAllSuppliers();
            return $this->successResponse($suppliers);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreSupplierRequest $request)
    {
        try {
            $supplier = $this->supplierService->createSupplier($request->validated());
            return $this->successResponse($supplier, 'Supplier created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $supplier = $this->supplierService->getSupplierById((int) $id);
            return $this->successResponse($supplier);
        } catch (\Exception $e) {
            return $this->errorResponse('Supplier not found', 404);
        }
    }

    public function update(UpdateSupplierRequest $request, $id)
    {
        try {
            $supplier = $this->supplierService->updateSupplier((int) $id, $request->validated());
            return $this->successResponse($supplier, 'Supplier updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->supplierService->deleteSupplier((int) $id);
            return $this->successResponse(null, 'Supplier deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Supplier not found', 404);
        }
    }
}