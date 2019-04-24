<?php namespace Sheba\Pos\Order\RefundNatures;

use Sheba\Pos\Order\Updater;

class ReturnPosItem extends RefundNature
{
    /**  @var Updater */
    private $updater;

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
    }

    public function update()
    {
        $this->updater->setOrder($this->order)->setData($this->data)->update();
    }
}