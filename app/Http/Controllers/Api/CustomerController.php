<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Services\CustomerService;
use App\Traits\ApiResponseTrait;

class CustomerController extends Controller
{
    use ApiResponseTrait;

    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index()
    {
        try {
            $customers = $this->customerService->getAllCustomers();
            return $this->successResponse($customers);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreCustomerRequest $request)
    {
        try {
            $imageFile = $request->file('profile_image');
            $customer = $this->customerService->createCustomer($request->validated(), $imageFile);
            return $this->successResponse($customer, 'Customer created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $customer = $this->customerService->getCustomerById((int) $id);
            return $this->successResponse($customer);
        } catch (\Exception $e) {
            return $this->errorResponse('Customer not found', 404);
        }
    }

    public function update(UpdateCustomerRequest $request, $id)
    {
        try {
            $imageFile = $request->file('profile_image');
            $customer = $this->customerService->updateCustomer((int) $id, $request->validated(), $imageFile);
            return $this->successResponse($customer, 'Customer updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->customerService->deleteCustomer((int) $id);
            return $this->successResponse(null, 'Customer deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Customer not found', 404);
        }
    }
}
