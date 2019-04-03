<?php

namespace Sheba\MovieTicket\Response;

use App\Models\MovieTicketOrder;

class BlockBusterFailResponse extends MovieTicketFailResponse
{

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function getMovieTicketOrder(): MovieTicketOrder
    {
        return MovieTicketOrder::where('transaction_id', 'like', '%' . $this->response->ticket_id . '%')->first();
    }

    public function getFailedTransactionDetails()
    {
        return $this->response;
    }
}