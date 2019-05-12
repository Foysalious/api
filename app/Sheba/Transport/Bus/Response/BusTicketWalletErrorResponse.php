<?php namespace Sheba\Transport\Bus\Response;

class BusTicketWalletErrorResponse extends BusTicketErrorResponse
{
    protected $errorCode = 421;
    protected $errorMessage = "Wallet exceeded.";
}