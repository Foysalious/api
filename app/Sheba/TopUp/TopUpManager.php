<?php namespace Sheba\TopUp;


use App\Models\TopUpOrder;
use Exception;
use Illuminate\Support\Facades\DB;
use Sheba\TopUp\Vendor\VendorFactory;
use Throwable;

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

    /**
     * @param $action
     * @throws Throwable
     */
    protected function doTransaction($action)
    {
        try {
            DB::transaction($action);
        } catch (Throwable $e) {
            $this->markOrderAsSystemError($e);
            throw $e;
        }
    }

    protected function markOrderAsSystemError(Throwable $e)
    {
        dde($e);
        logErrorWithExtra($e, ['topup' => $this->topUpOrder->getDirty()]);
        $this->statusChanger->systemError();
    }

    protected function refund()
    {
        $this->topUpOrder->agent->getCommission()->setTopUpOrder($this->topUpOrder)->refund();
    }

    /**
     * @return Vendor\Vendor
     * @throws Exception
     */
    protected function getVendor()
    {
        return (new VendorFactory())->getById($this->topUpOrder->vendor_id);
    }
}
