<?php namespace Sheba\TopUp\Vendor;

use App\Models\TopUpOrder;
use App\Models\TopUpRechargeHistory;
use App\Models\TopUpVendor;
use Carbon\Carbon;
use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Gateway\GatewayFactory;
use Sheba\TopUp\Gateway\Names;
use Sheba\TopUp\Gateway\Ssl;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

abstract class Vendor
{
    protected $model;
    /** @var Gateway */
    protected $topUpGateway;

    public function setModel(TopUpVendor $model)
    {
        $this->model = $model;
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

    public function recharge(TopUpOrder $topup_order)
    {
        $this->resolveGateway($topup_order);
        return $this->topUpGateway->recharge($topup_order);
    }

    private function resolveGateway(TopUpOrder $topUpOrder)
    {
        $gateway_factory = new GatewayFactory();
        $gateway_factory->setGatewayName($topUpOrder->gateway);
        $this->setTopUpGateway($gateway_factory->get());
    }

    private function setTopUpGateway(Gateway $topup_gateway)
    {
        $this->topUpGateway = $topup_gateway;
        return $this;
    }

    public function getTopUpInitialStatus()
    {
        return $this->topUpGateway->getInitialStatus();
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
    }
}