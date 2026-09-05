<?php

namespace App\Repositories\Eloquent;

use App\Models\Partner;
use App\Repositories\Contracts\PartnerRepositoryInterface;

class PartnerRepository implements PartnerRepositoryInterface
{
    public function getAll()
    {
        return Partner::orderBy('partner_id', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Partner::findOrFail($id);
    }

    public function create(array $data)
    {
        return Partner::create($data);
    }

    public function update(int $id, array $data)
    {
        $partner = Partner::findOrFail($id);
        $partner->update($data);
        return $partner->fresh();
    }

    public function delete(int $id)
    {
        $partner = Partner::findOrFail($id);
        return $partner->delete();
    }
}