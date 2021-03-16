<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosServiceDiscount;
use Sheba\Repositories\BaseRepository;

class PosServiceDiscountRepository extends BaseRepository
{
    public function __construct(PartnerPosServiceDiscount $partner_pos_service_discount)
    {
        parent::__construct();
        $this->setModel($partner_pos_service_discount);
    }

    public function save($data)
    {
        return PartnerPosServiceDiscount::create($this->withCreateModificationField($data));
    }

}