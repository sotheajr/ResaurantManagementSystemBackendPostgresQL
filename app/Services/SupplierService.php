<?php

namespace App\Services;

use App\Repositories\Contracts\SupplierRepositoryInterface;

class SupplierService
{
    protected $supplierRepository;

    public function __construct(SupplierRepositoryInterface $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function getAllSuppliers()
    {
        return $this->supplierRepository->getAll();
    }

    public function getSupplierById(int $id)
    {
        return $this->supplierRepository->findById($id);
    }

    public function createSupplier(array $data)
    {
        return $this->supplierRepository->create($data);
    }

    public function updateSupplier(int $id, array $data)
    {
        return $this->supplierRepository->update($id, $data);
    }

    public function deleteSupplier(int $id)
    {
        return $this->supplierRepository->delete($id);
    }
}