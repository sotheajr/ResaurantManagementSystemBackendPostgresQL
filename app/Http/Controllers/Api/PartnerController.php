<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Services\PartnerService;
use App\Traits\ApiResponseTrait;

class PartnerController extends Controller
{
    use ApiResponseTrait;

    protected $partnerService;

    public function __construct(PartnerService $partnerService)
    {
        $this->partnerService = $partnerService;
    }

    public function index()
    {
        try {
            $partners = $this->partnerService->getAllPartners();
            return $this->successResponse($partners);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StorePartnerRequest $request)
    {
        try {
            $partner = $this->partnerService->createPartner($request->validated(), $request->file('image'));
            return $this->successResponse($partner, 'Partner created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $partner = $this->partnerService->getPartnerById((int) $id);
            return $this->successResponse($partner);
        } catch (\Exception $e) {
            return $this->errorResponse('Partner not found', 404);
        }
    }

    public function update(UpdatePartnerRequest $request, $id)
    {
        try {
            $partner = $this->partnerService->updatePartner((int) $id, $request->validated(), $request->file('image'));
            return $this->successResponse($partner, 'Partner updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->partnerService->deletePartner((int) $id);
            return $this->successResponse(null, 'Partner deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Partner not found', 404);
        }
    }
}