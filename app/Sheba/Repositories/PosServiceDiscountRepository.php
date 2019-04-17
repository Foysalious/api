<?php namespace Sheba\Repositories;

use App\Models\PartnerPosServiceDiscount;

class PosServiceDiscountRepository extends BaseRepository
{
    public function save($data)
    {
        return PartnerPosServiceDiscount::create($this->withCreateModificationField($data));
    }
}