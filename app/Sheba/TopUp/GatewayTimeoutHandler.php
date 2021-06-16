<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use App\Models\User;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Illuminate\Database\Eloquent\Collection;
use Sheba\Dal\TopUpGateway\TopUpGatewayRepository;
use Sheba\Dal\TopupOrder\TopUpOrderRepository;
use Sheba\Dal\TopupVendor\TopUpVendorRepository;
use Sheba\Sms\Sms;
use Sheba\TopUp\Gateway\Gateway;

class GatewayTimeoutHandler
{
    const TIMEOUT_THRESHOLD_COUNT = 5;

    /** @var TopUpOrderRepository */
    private $topUpOrderRepo;
    /** @var TopUpVendorRepository */
    private $vendorRepo;
    /** @var TopUpGatewayRepository */
    private $gatewayRepo;

    /** @var Gateway */
    private $gateway;
    /** @var TopUpOrder */
    private $topUpOrder;

    /**
     * GatewayTimeoutHandler constructor.
     *
     * @param TopUpOrderRepository $top_up_repo
     * @param TopUpVendorRepository $vendor_repo
     * @param TopUpGatewayRepository $gateway_repo
     */
    public function __construct(TopUpOrderRepository $top_up_repo, TopUpVendorRepository $vendor_repo, TopUpGatewayRepository $gateway_repo)
    {
        $this->topUpOrderRepo = $top_up_repo;
        $this->vendorRepo = $vendor_repo;
        $this->gatewayRepo = $gateway_repo;
    }

    public function setGateway(Gateway $gateway)
    {
        $this->gateway = $gateway;
        return $this;
    }

    public function setTopUpOrder(TopUpOrder $top_up_order)
    {
        $this->topUpOrder = $top_up_order;
        return $this;
    }

    public function handle()
    {
        $n = self::TIMEOUT_THRESHOLD_COUNT - 1;
        $top_ups = $this->topUpOrderRepo->getPreviousNOrdersUsingGateway($n, $this->gateway->getName(), $this->topUpOrder);

        if ($top_ups->count() != $n || $this->isAllNotTimedOut($top_ups)) return;

        $this->vendorRepo->unpublishAllUsingGateway($this->gateway->getName());
        $this->notify();
    }

    /**
     * @param Collection $top_up_orders
     * @return bool
     */
    private function isAllNotTimedOut(Collection $top_up_orders)
    {
        return !$this->isAllTimedOut($top_up_orders);
    }

    /**
     * @param Collection $top_up_orders
     * @return bool
     */
    private function isAllTimedOut(Collection $top_up_orders)
    {
        $result = true;
        $top_up_orders->each(function (TopUpOrder $top_up_order) use (&$result) {
            if ($top_up_order->isFailedDueToGatewayTimeout()) return;

            $result = false;
            return false;
        });
        return $result;
    }

    private function notify()
    {
        $gateway = $this->gateway->getName();
        $message = "All top up operators using $gateway gateway has been unpublished due to connection timeout. Please take necessary actions.";
        $this->gatewayRepo->findByName($gateway)->smsReceivers->each(function (User $user) use ($message) {
            (new Sms())
                ->setBusinessType(BusinessType::BONDHU)
                ->setFeatureType(FeatureType::TOP_UP)
                ->msg($message)
                ->to($user->phone)
                ->shoot();
        });
    }
}
