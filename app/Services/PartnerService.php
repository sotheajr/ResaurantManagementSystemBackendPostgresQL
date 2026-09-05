<?php

namespace App\Services;

use App\Repositories\Contracts\PartnerRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Http\UploadedFile;

class PartnerService
{
    use UploadImageTrait;

    protected $partnerRepository;

    public function __construct(PartnerRepositoryInterface $partnerRepository)
    {
        $this->partnerRepository = $partnerRepository;
    }

    public function getAllPartners()
    {
        return $this->partnerRepository->getAll();
    }

    public function getPartnerById(int $id)
    {
        return $this->partnerRepository->findById($id);
    }

    public function createPartner(array $data, ?UploadedFile $imageFile = null)
    {
        if ($imageFile) {
            $data['image'] = $this->uploadImage($imageFile, 'partners');
        }

        return $this->partnerRepository->create($data);
    }

    public function updatePartner(int $id, array $data, ?UploadedFile $imageFile = null)
    {
        $partner = $this->partnerRepository->findById($id);

        if ($imageFile) {
            // Delete the old logo from storage before saving the new one
            if ($partner->image) {
                $this->deleteImage($partner->image);
            }
            $data['image'] = $this->uploadImage($imageFile, 'partners');
        }

        return $this->partnerRepository->update($id, $data);
    }

    public function deletePartner(int $id)
    {
        $partner = $this->partnerRepository->findById($id);

        // Delete the associated logo from storage
        if ($partner->image) {
            $this->deleteImage($partner->image);
        }

        return $this->partnerRepository->delete($id);
    }
}