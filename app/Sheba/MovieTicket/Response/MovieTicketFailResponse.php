<?php namespace Sheba\MovieTicket\Response;

use App\Models\MovieTicketOrder;

abstract class MovieTicketFailResponse
{
    /** @var array $response */
    protected $response;

    abstract public function setResponse($response);

    abstract public function getMovieTicketOrder(): MovieTicketOrder;

    abstract public function getFailedTransactionDetails();
}