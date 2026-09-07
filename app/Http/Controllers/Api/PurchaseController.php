<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Services\PurchaseService;
use App\Traits\ApiResponseTrait;

class PurchaseController extends Controller
{
    use ApiResponseTrait;

    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index()
    {
        try {
            $purchases = $this->purchaseService->getAllPurchases();
            return $this->successResponse($purchases);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StorePurchaseRequest $request)
    {
        try {
            $items = $request->validated()['items'] ?? $request->input('items', []);
            // Check for file in multiple possible field names
            $invoiceFile = $request->file('invoice_attachment') ?? $request->file('image') ?? $request->file('receipt_image');
            $purchase = $this->purchaseService->createPurchase(
                collect($request->validated())->except(['items'])->toArray(),
                $items,
                $invoiceFile
            );
            // Load relationships for the response
            return $this->successResponse($purchase->load(['supplier', 'partner']), 'Purchase created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $purchase = $this->purchaseService->getPurchaseById((int) $id);
            return $this->successResponse($purchase);
        } catch (\Exception $e) {
            return $this->errorResponse('Purchase not found', 404);
        }
    }

    public function update(UpdatePurchaseRequest $request, $id)
    {
        try {
            $items = $request->input('items', []);
            // Check for file in multiple possible field names
            $invoiceFile = $request->file('invoice_attachment') ?? $request->file('image') ?? $request->file('receipt_image');
            $purchase = $this->purchaseService->updatePurchase(
                (int) $id,
                collect($request->validated())->except(['items'])->toArray(),
                $items,
                $invoiceFile
            );
            // Load relationships for the response
            return $this->successResponse($purchase->load(['supplier', 'partner']), 'Purchase updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->purchaseService->deletePurchase((int) $id);
            return $this->successResponse(null, 'Purchase deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Purchase not found', 404);
        }
    }
}
