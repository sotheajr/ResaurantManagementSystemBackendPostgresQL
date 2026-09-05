<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryRequest;
use App\Http\Requests\Inventory\UpdateInventoryRequest;
use App\Services\InventoryService;
use App\Traits\ApiResponseTrait;

class InventoryController extends Controller
{
    use ApiResponseTrait;

    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        try {
            $items = $this->inventoryService->getAllInventory();
            return $this->successResponse($items);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function lowStock()
    {
        try {
            $items = $this->inventoryService->getLowStockItems();
            return $this->successResponse($items);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreInventoryRequest $request)
    {
        try {
            $item = $this->inventoryService->createInventory($request->validated());
            return $this->successResponse($item, 'Inventory item created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $item = $this->inventoryService->getInventoryById((int) $id);
            return $this->successResponse($item);
        } catch (\Exception $e) {
            return $this->errorResponse('Inventory item not found', 404);
        }
    }

    public function update(UpdateInventoryRequest $request, $id)
    {
        try {
            $item = $this->inventoryService->updateInventory((int) $id, $request->validated());
            return $this->successResponse($item, 'Inventory item updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function updateStock($id)
    {
        try {
            $quantity = request()->input('quantity', 0);
            $item = $this->inventoryService->updateStock((int) $id, (float) $quantity);
            return $this->successResponse($item, 'Stock updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->inventoryService->deleteInventory((int) $id);
            return $this->successResponse(null, 'Inventory item deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Inventory item not found', 404);
        }
    }
}