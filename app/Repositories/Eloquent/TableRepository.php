<?php

namespace App\Repositories\Eloquent;

use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;

class TableRepository implements TableRepositoryInterface
{
    public function getAll()
    {
        return Table::orderBy('table_number', 'asc')->get();
    }

    public function findById(int $id)
    {
        return Table::findOrFail($id);
    }

    public function getAvailableTables()
    {
        return Table::where('status', 'Available')->orderBy('table_number', 'asc')->get();
    }

    public function create(array $data)
    {
        return Table::create($data);
    }

    public function update(int $id, array $data)
    {
        $table = Table::findOrFail($id);
        $table->update($data);
        return $table->fresh();
    }

    public function delete(int $id)
    {
        $table = Table::findOrFail($id);
        return $table->delete();
    }
}
