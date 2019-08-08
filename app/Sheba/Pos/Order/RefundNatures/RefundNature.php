<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;
use Sheba\Pos\Log\Creator as LogCreator;
use Sheba\Pos\Order\Updater;

abstract class RefundNature
{
    /** @var PosOrder $order */
    public $order;
    /** @var array $data */
    public $data;
    /** @var LogCreator $logCreator */
    protected $logCreator;
    /** @var Updater */
    public $updater;
    protected $services;

    public function __construct(LogCreator $log_creator, Updater $updater)
    {
        $this->logCreator = $log_creator;
        $this->updater = $updater;
    }

    /**
     * @param PosOrder $order
     * @return $this
     */
    public function setOrder(PosOrder $order)
    {
        $this->order = $order->calculate();
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->services = $this->setServices();

        return $this;
    }

    public function setServices()
    {
        return collect(json_decode($this->data['services']));
    }

    protected abstract function saveLog();

    public abstract function update();
}