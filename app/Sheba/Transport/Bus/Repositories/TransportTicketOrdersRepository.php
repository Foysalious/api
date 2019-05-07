<?php namespace Sheba\Transport\Bus\Repositories;

use App\Models\Transport\TransportTicketOrder;
use Sheba\Repositories\BaseRepository;

class TransportTicketOrdersRepository extends BaseRepository
{
    /**
     * @param $id
     * @return TransportTicketOrder
     */
    public function findById($id)
    {
        return TransportTicketOrder::find($id);
    }

    /**
     * @param array $data
     * @return TransportTicketOrder
     */
    public function save(array $data)
    {
        return TransportTicketOrder::create($this->withCreateModificationField($data));
    }
}