<?php namespace Sheba\Transport\Bus\Response;

class BusTicketWalletErrorResponse extends MovieTicketErrorResponse
{
    protected $errorCode = 421;
    protected $errorMessage = "Wallet exceeded.";
}