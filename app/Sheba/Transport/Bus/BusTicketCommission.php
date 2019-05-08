<?php namespace Sheba\Transport\Bus;

abstract class BusTicketCommission
{
    abstract public function disburse();

    abstract public function refund();
}