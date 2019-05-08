<?php namespace Sheba\Transport\Bus\Response;

use App\Models\MovieTicketOrder;

abstract class BusTicketFailResponse
{
    /** @var array $response */
    protected $response;

    abstract public function setResponse($response);

    abstract public function getMovieTicketOrder(): MovieTicketOrder;

    abstract public function getFailedTransactionDetails();
}