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

    /**
     * Update a specified resource.
     * @param TransportTicketOrder $transport_ticket_order
     * @param array $data
     * @return bool|int
     */
    public function update(TransportTicketOrder $transport_ticket_order, array $data)
    {
        return $transport_ticket_order->update($this->withUpdateModificationField($data));
    }
}