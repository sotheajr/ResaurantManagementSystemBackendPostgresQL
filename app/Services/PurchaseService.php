<?php

namespace App\Services;

use App\Repositories\Contracts\PurchaseRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Http\UploadedFile;

class PurchaseService
{
    use UploadImageTrait;

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

    public function createPurchase(array $data, array $items, ?UploadedFile $invoiceFile = null)
    {
        if ($invoiceFile) {
            $data['invoice_attachment'] = $this->uploadImage($invoiceFile, 'purchases');
        }

        return $this->purchaseRepository->create($data, $items);
    }

    public function updatePurchase(int $id, array $data, array $items, ?UploadedFile $invoiceFile = null)
    {
        $purchase = $this->purchaseRepository->findById($id);

        if ($invoiceFile) {
            if ($purchase->invoice_attachment) {
                $this->deleteImage($purchase->invoice_attachment);
            }
            $data['invoice_attachment'] = $this->uploadImage($invoiceFile, 'purchases');
        }

        return $this->purchaseRepository->update($id, $data, $items);
    }

    public function deletePurchase(int $id)
    {
        $purchase = $this->purchaseRepository->findById($id);

        if ($purchase->invoice_attachment) {
            $this->deleteImage($purchase->invoice_attachment);
        }

        return $this->purchaseRepository->delete($id);
    }
}
