<?php namespace Sheba\Transport\Bus\Response;

class BdTicketsResponse extends BusTicketResponse
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
        $bus_ticket_error_response->status = $this->response->status;
        $bus_ticket_error_response->errorMessage = $this->response->message;

        return $bus_ticket_error_response;
    }
}