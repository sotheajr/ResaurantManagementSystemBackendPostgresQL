<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\StoreMenuItemRequest;
use App\Http\Requests\Menu\UpdateMenuItemRequest;
use App\Services\MenuItemService;
use App\Traits\ApiResponseTrait;

class MenuItemController extends Controller
{
    use ApiResponseTrait;

    protected $menuItemService;

    public function __construct(MenuItemService $menuItemService)
    {
        $this->menuItemService = $menuItemService;
    }

    public function index()
    {
        try {
            $items = $this->menuItemService->getAllItems();
            return $this->successResponse($items);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function available()
    {
        try {
            $items = $this->menuItemService->getAvailableItems();
            return $this->successResponse($items);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreMenuItemRequest $request)
    {
        try {
            $imageFile = $request->file('image');
            $item = $this->menuItemService->createItem($request->validated(), $imageFile);
            return $this->successResponse($item, 'Menu item created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $item = $this->menuItemService->getItemById((int) $id);
            return $this->successResponse($item);
        } catch (\Exception $e) {
            return $this->errorResponse('Menu item not found', 404);
        }
    }

    public function update(UpdateMenuItemRequest $request, $id)
    {
        try {
            $imageFile = $request->file('image');
            $item = $this->menuItemService->updateItem((int) $id, $request->validated(), $imageFile);
            return $this->successResponse($item, 'Menu item updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->menuItemService->deleteItem((int) $id);
            return $this->successResponse(null, 'Menu item deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Menu item not found', 404);
        }
    }
}
