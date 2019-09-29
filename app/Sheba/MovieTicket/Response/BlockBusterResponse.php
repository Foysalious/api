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
        return $this->response->status == "Seat-CONFIRM-Successfully";
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
        if ($this->hasSuccess()) throwException(new \Exception('Response has success'));;
        $movie_ticket_error_response = new MovieTicketErrorResponse();
        $movie_ticket_error_response->status = $this->response->status;
        $movie_ticket_error_response->errorMessage = $this->response->message;
        return $movie_ticket_error_response;
    }
}