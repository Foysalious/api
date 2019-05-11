<?php namespace Sheba\Transport\Bus\Response;

use App\Models\Transport\TransportTicketOrder;

class BdTicketsFailResponse extends BusTicketFailResponse
{
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function getFailedTransactionDetails()
    {
        return $this->response;
    }

    public function getTransportTicketOrder(): TransportTicketOrder
    {
        return TransportTicketOrder::where('transaction_id', 'like', '%' . $this->response->ticket_id . '%')->first();
    }
}