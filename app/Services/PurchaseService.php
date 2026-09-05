<?php

namespace App\Services;

use App\Repositories\Contracts\PurchaseRepositoryInterface;

class PurchaseService
{
    protected $purchaseRepository;

    public function __construct(PurchaseRepositoryInterface $purchaseRepository)
    {
        $this->purchaseRepository = $purchaseRepository;
    }

    public function getAllPurchases()
    {
        return $this->purchaseRepository->getAll();
    }

    public function getPurchaseById(int $id)
    {
        return $this->purchaseRepository->findById($id);
    }

    public function createPurchase(array $data, array $items)
    {
        return $this->purchaseRepository->create($data, $items);
    }

    public function updatePurchase(int $id, array $data, array $items)
    {
        return $this->purchaseRepository->update($id, $data, $items);
    }

    public function deletePurchase(int $id)
    {
        return $this->purchaseRepository->delete($id);
    }
}