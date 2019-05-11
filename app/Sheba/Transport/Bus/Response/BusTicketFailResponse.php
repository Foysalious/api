<?php namespace Sheba\Transport\Bus\Response;

use App\Models\Transport\TransportTicketOrder;

abstract class BusTicketFailResponse
{
    /** @var array $response */
    protected $response;

    abstract public function setResponse($response);

    abstract public function getTransportTicketOrder(): TransportTicketOrder;

    abstract public function getFailedTransactionDetails();
}