<?php namespace Sheba\MovieTicket\Response;


class BlockBusterResponse extends MovieResponse
{
    protected $response;
    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function hasSuccess(): bool
    {
        return $this->response->status_code == 0000;
    }

    public function getSuccess(): MovieTicketSuccessResponse
    {
        $movie_ticket_success_response = new MovieTicketSuccessResponse();
        $movie_ticket_success_response->transactionId = $this->response->trx_id;
        $movie_ticket_success_response->transactionDetails = $this->response;
        return $movie_ticket_success_response;
    }

    public function getError(): MovieTicketErrorResponse
    {
        if ($this->hasSuccess()) throwException(new \Exception('Response has success'));
        $movie_ticket_error_response = new MovieTicketErrorResponse();
        $movie_ticket_error_response->errorCode = $this->response->TXNID;
        $movie_ticket_error_response->errorMessage = isset($this->response->status) ? $this->response->status : 'Error message not given.';
        return $movie_ticket_error_response;
    }
}