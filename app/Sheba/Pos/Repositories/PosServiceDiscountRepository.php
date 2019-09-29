<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosServiceDiscount;
use Sheba\Repositories\BaseRepository;

class PosServiceDiscountRepository extends BaseRepository
{
    public function save($data)
    {
        return PartnerPosServiceDiscount::create($this->withCreateModificationField($data));
    }

}