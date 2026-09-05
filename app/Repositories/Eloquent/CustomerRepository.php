<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function getAll()
    {
        return Customer::orderBy('id', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Customer::findOrFail($id);
    }

    public function create(array $data)
    {
        return Customer::create($data);
    }

    public function update(int $id, array $data)
    {
        $customer = Customer::findOrFail($id);
        $customer->update($data);
        return $customer->fresh();
    }

    public function delete(int $id)
    {
        $customer = Customer::findOrFail($id);
        return $customer->delete();
    }
}
