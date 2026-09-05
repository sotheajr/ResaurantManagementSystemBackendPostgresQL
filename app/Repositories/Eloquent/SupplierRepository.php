<?php

namespace App\Repositories\Eloquent;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function getAll()
    {
        return Supplier::orderBy('supplier_id', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Supplier::findOrFail($id);
    }

    public function create(array $data)
    {
        return Supplier::create($data);
    }

    public function update(int $id, array $data)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier->fresh();
    }

    public function delete(int $id)
    {
        $supplier = Supplier::findOrFail($id);
        return $supplier->delete();
    }
}