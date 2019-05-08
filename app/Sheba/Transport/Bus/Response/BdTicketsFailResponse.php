<?php namespace Sheba\Transport\Bus\Response;

use App\Models\Transport\TransportTicketOrder;

class BdTicketsFailResponse extends BusTicketFailResponse
{
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function getMovieTicketOrder(): TransportTicketOrder
    {
        return TransportTicketOrder::where('transaction_id', 'like', '%' . $this->response->ticket_id . '%')->first();
    }

    public function getFailedTransactionDetails()
    {
        return $this->response;
    }
}