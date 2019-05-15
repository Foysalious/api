<?php namespace Sheba\Pos\Repositories;

use App\Models\PosCustomer;
use Sheba\Repositories\BaseRepository;

class PosCustomerRepository extends BaseRepository
{
    /**
     * @param array $data
     * @return PosCustomer
     */
    public function save(array $data)
    {
        return PosCustomer::create($this->withCreateModificationField($data));
    }
}