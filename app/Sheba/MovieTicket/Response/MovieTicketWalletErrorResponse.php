<?php namespace Sheba\MovieTicket\Response;

class MovieTicketWalletErrorResponse extends MovieTicketErrorResponse
{
    protected $errorCode = 421;
    protected $errorMessage = "Wallet exceeded.";
}