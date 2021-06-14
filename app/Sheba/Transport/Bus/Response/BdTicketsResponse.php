<?php namespace Sheba\Transport\Bus\Response;

class BdTicketsResponse extends BusTicketResponse
{
    protected $response;
    protected $exception;

    public function setResponse($response)
    {
        $this->response = $response;
        $this->exception = $this->response;

        return $this;
    }

    public function hasSuccess(): bool
    {
        return is_null($this->exception->errors);
    }

    public function getSuccess(): BusTicketSuccessResponse
    {
        $movie_ticket_success_response = new BusTicketSuccessResponse();
        $movie_ticket_success_response->transactionId = $this->response->trx_id;
        $movie_ticket_success_response->transactionDetails = $this->response;

        return $movie_ticket_success_response;
    }

    public function getError(): BusTicketErrorResponse
    {
        if ($this->hasSuccess()) throwException(new \Exception('Response has success'));;
        $bus_ticket_error_response = new BusTicketErrorResponse();
        $bus_ticket_error_response->status = $this->response->getCode();
        $bus_ticket_error_response->errorMessage = $this->exception->errors[0];

        return $bus_ticket_error_response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}