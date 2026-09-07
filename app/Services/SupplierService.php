<?php

namespace App\Services;

use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Http\UploadedFile;

class SupplierService
{
    use UploadImageTrait;

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

    public function createSupplier(array $data, ?UploadedFile $logoFile = null)
    {
        if ($logoFile) {
            $data['logo'] = $this->uploadImage($logoFile, 'suppliers');
        }

        return $this->supplierRepository->create($data);
    }

    public function updateSupplier(int $id, array $data, ?UploadedFile $logoFile = null)
    {
        $supplier = $this->supplierRepository->findById($id);

        if ($logoFile) {
            if ($supplier->logo) {
                $this->deleteImage($supplier->logo);
            }
            $data['logo'] = $this->uploadImage($logoFile, 'suppliers');
        }

        return $this->supplierRepository->update($id, $data);
    }

    public function deleteSupplier(int $id)
    {
        $supplier = $this->supplierRepository->findById($id);

        if ($supplier->logo) {
            $this->deleteImage($supplier->logo);
        }

        return $this->supplierRepository->delete($id);
    }
}
