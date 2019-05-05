<?php namespace Sheba\Transport\Bus\Repositories;

use App\Models\Transport\TransportTicketOrder;
use Sheba\Repositories\BaseRepository;

class TransportTicketOrdersRepository extends BaseRepository
{
    /**
     * @param array $data
     * @return TransportTicketOrder
     */
    public function save(array $data)
    {
        return TransportTicketOrder::create($this->withCreateModificationField($data));
    }
}