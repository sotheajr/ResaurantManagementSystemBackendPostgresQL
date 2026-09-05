<?php

namespace App\Services;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Traits\UploadImageTrait;
use Illuminate\Http\UploadedFile;

class CustomerService
{
    use UploadImageTrait;

    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getAllCustomers()
    {
        return $this->customerRepository->getAll();
    }

    public function getCustomerById(int $id)
    {
        return $this->customerRepository->findById($id);
    }

    public function createCustomer(array $data, ?UploadedFile $imageFile = null)
    {
        if ($imageFile) {
            $data['profile_image'] = $this->uploadImage($imageFile, 'customers');
        }

        return $this->customerRepository->create($data);
    }

    public function updateCustomer(int $id, array $data, ?UploadedFile $imageFile = null)
    {
        $customer = $this->customerRepository->findById($id);

        if ($imageFile) {
            if ($customer->profile_image) {
                $this->deleteImage($customer->profile_image);
            }
            $data['profile_image'] = $this->uploadImage($imageFile, 'customers');
        }

        return $this->customerRepository->update($id, $data);
    }

    public function deleteCustomer(int $id)
    {
        $customer = $this->customerRepository->findById($id);

        if ($customer->profile_image) {
            $this->deleteImage($customer->profile_image);
        }

        return $this->customerRepository->delete($id);
    }
}
