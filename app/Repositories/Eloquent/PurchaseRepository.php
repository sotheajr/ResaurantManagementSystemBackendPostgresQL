<?php

namespace App\Repositories\Eloquent;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    public function getAll()
    {
        return Purchase::with('supplier', 'partner', 'purchaseItems.inventory')->orderBy('purchase_date', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Purchase::with('supplier', 'partner', 'purchaseItems.inventory')->findOrFail($id);
    }

    public function create(array $data, array $items = [])
    {
        return DB::transaction(function () use ($data, $items) {
            $purchase = Purchase::create($data);

            // Only create purchase items if items are provided
            if (!empty($items)) {
                foreach ($items as $item) {
                    $purchaseItem = new PurchaseItem([
                        'purchase_id' => $purchase->purchase_id,
                        'inventory_id' => $item['inventory_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['quantity'] * $item['unit_price'],
                    ]);

                    $purchaseItem->save();

                    // Update inventory quantity (add purchased quantity)
                    $inventory = \App\Models\Inventory::findOrFail($item['inventory_id']);
                    $inventory->increment('quantity', $item['quantity']);
                }
            }

            return $purchase->load('supplier', 'partner', 'purchaseItems.inventory');
        });
    }

    public function update(int $id, array $data, array $items = [])
    {
        return DB::transaction(function () use ($id, $data, $items) {
            $purchase = Purchase::findOrFail($id);

            // Revert previous stock changes
            foreach ($purchase->purchaseItems as $oldItem) {
                if ($oldItem->inventory) {
                    $oldItem->inventory->decrement('quantity', $oldItem->quantity);
                }
            }

            // Delete existing purchase items
            PurchaseItem::where('purchase_id', $purchase->purchase_id)->delete();

            // Update purchase
            $purchase->update($data);

            // Only create purchase items if items are provided
            if (!empty($items)) {
                foreach ($items as $item) {
                    $subtotal = $item['quantity'] * $item['unit_price'];

                    $purchaseItem = new PurchaseItem([
                        'purchase_id' => $purchase->purchase_id,
                        'inventory_id' => $item['inventory_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $subtotal,
                    ]);

                    $purchaseItem->save();

                    // Update inventory quantity (add new quantity)
                    $inventory = \App\Models\Inventory::findOrFail($item['inventory_id']);
                    $inventory->increment('quantity', $item['quantity']);
                }
            }

            return $purchase->load('supplier', 'partner', 'purchaseItems.inventory');
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $purchase = Purchase::findOrFail($id);

            // Revert stock changes
            foreach ($purchase->purchaseItems as $item) {
                if ($item->inventory) {
                    $item->inventory->decrement('quantity', $item->quantity);
                }
            }

            return $purchase->delete();
        });
    }
}