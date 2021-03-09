<?php namespace Sheba\TopUp;


use App\Models\TopUpOrder;
use Exception;

abstract class TopUpManager
{
    /** @var StatusChanger */
    protected $statusChanger;

    /** @var TopUpOrder */
    protected $topUpOrder;

    public function __construct(StatusChanger $status_changer)
    {
        $this->statusChanger = $status_changer;
    }

    /**
     * @param TopUpOrder $order
     * @return $this
     */
    public function setTopUpOrder(TopUpOrder $order)
    {
        $this->topUpOrder = $order;
        $this->statusChanger->setOrder($this->topUpOrder);
        return $this;
    }

    protected function markOrderAsSystemError(Exception $e)
    {
        logErrorWithExtra($e, ['topup' => $this->topUpOrder->getDirty()]);
        $this->statusChanger->systemError();
    }

    protected function refund()
    {
        $this->topUpOrder->agent->getCommission()->setTopUpOrder($this->topUpOrder)->refund();
    }
}
