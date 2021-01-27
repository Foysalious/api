<?php namespace Sheba\TopUp\Vendor;

use App\Models\TopUpOrder;
use App\Models\TopUpRechargeHistory;
use App\Models\TopUpVendor;
use Carbon\Carbon;
use Exception;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Gateway\GatewayFactory;
use Sheba\TopUp\Gateway\Names;
use Sheba\TopUp\Gateway\Ssl;
use Sheba\TopUp\Vendor\Response\GenericGatewayErrorResponse;
use Sheba\TopUp\Vendor\Response\TopUpGatewayTimeoutResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

abstract class Vendor
{
    protected $model;
    /** @var Gateway */
    protected $topUpGateway;

    public function setModel(TopUpVendor $model)
    {
        $this->model = $model;
        $this->setTopUpGateway(app(Ssl::class));
        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function isPublished()
    {
        return $this->model->is_published;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $this->resolveGateway($topup_order);
        try {
            return $this->topUpGateway->recharge($topup_order);
        } catch (GatewayTimeout $e) {
            return (new GenericGatewayErrorResponse())->setErrorResponse(new TopUpGatewayTimeoutResponse());
        }
    }

    public function getTopUpInitialStatus()
    {
        return $this->topUpGateway->getInitialStatus();
    }

    protected function createNewRechargeHistory($amount, $vendor_id = null)
    {
        $recharge_history = new TopUpRechargeHistory();
        $recharge_history->recharge_date = Carbon::now();
        $recharge_history->vendor_id = $vendor_id ?: $this->model->id;
        $recharge_history->amount = $amount;
        $recharge_history->save();
    }

    public function deductAmount($amount)
    {
        $this->model->amount -= $amount;
        $this->model->update();
    }

    public function refill($amount)
    {
        $this->model->amount += $amount;
        $this->model->update();
        // $this->createNewRechargeHistory($amount);
    }

    private function resolveGateway(TopUpOrder $topUpOrder)
    {
        $gateway_factory = new GatewayFactory();
        $gateway_factory->setGatewayName($topUpOrder->gateway)->setVendorId($topUpOrder->vendor_id);
        $this->setTopUpGateway($gateway_factory->get());
    }

    private function setTopUpGateway(Gateway $topup_gateway)
    {
        $this->topUpGateway = $topup_gateway;
    }
}
