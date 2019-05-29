<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosCustomer;
use Sheba\Repositories\BaseRepository;

class PartnerPosCustomerRepository extends BaseRepository
{
    /**
     * @param array $data
     * @return PartnerPosCustomer
     */
    public function save(array $data)
    {
        return PartnerPosCustomer::create($this->withCreateModificationField($data));
    }
}