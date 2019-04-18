<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosServiceDiscount;
use Sheba\Repositories\BaseRepository;

class PosServiceDiscountRepository extends BaseRepository
{
    public function save($data)
    {
        return PartnerPosServiceDiscount::create($this->withCreateModificationField($data));
    }


    /**
     * @param PartnerPosServiceDiscount $discount
     * @param $data
     * @return bool|int
     */
    public function update(PartnerPosServiceDiscount $discount, $data)
    {
        return $discount->update($this->withUpdateModificationField($data));
    }
}