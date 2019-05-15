<?php namespace Sheba\Pos\Order\RefundNatures;

use Sheba\Pos\Log\Supported\Types;

class FullReturnPosItem extends ReturnPosItem
{
    protected function saveLog()
    {
        $this->logCreator->setOrder($this->order)
            ->setType(Types::FULL_RETURN)
            ->setLog("Order item returned partially, order id: {$this->order->id}")
            ->setDetails($this->details)
            ->create();
    }
}