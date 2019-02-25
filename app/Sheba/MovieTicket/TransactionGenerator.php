<?php namespace  Sheba\MovieTicket;


class TransactionGenerator
{
    private $uniqueIdentifier = 'SHB';

    public function generate()
    {
        return $this->uniqueIdentifier.time().sprintf('%08d',  rand(0,5000));
    }
}