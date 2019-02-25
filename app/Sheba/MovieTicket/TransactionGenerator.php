<?php namespace  Sheba\MovieTicket;


class TransactionGenerator
{
    private $uniqueIdentifier = 'SHB';
    private $counter = 0;

    public function generate()
    {
        return $this->uniqueIdentifier.time().sprintf('%08d', ++$this->counter);
    }
}