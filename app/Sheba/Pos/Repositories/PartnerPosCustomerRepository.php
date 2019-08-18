<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosCustomer;
use Sheba\Repositories\BaseRepository;

class PartnerPosCustomerRepository extends BaseRepository
{
    public function __construct(PartnerPosCustomer $partner_pos_customer)
    {
        parent::__construct();
        $this->setModel($partner_pos_customer);
    }

    /**
     * @param array $data
     * @return PartnerPosCustomer
     */
    public function save(array $data)
    {
        return PartnerPosCustomer::create($this->withCreateModificationField($data));
    }
}