<?php namespace Sheba\TopUp\Jobs;

use App\Jobs\Job;
use App\Models\TopUpOrder;
use App\Repositories\SmsHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Dal\TopUpGateway\Model as TopUpGateway;
use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Gateway\GatewayFactory;

class TopUpBalanceUpdateAndNotifyJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var TopUpGateway */
    protected $topUpGateway;
    protected $balance;
    /** @var TopUpOrder */
    protected $topup_order;
    /**  @var Gateway */
    protected $topUpGatewayFactory;

    public function __construct(TopUpOrder $topup_order)
    {
        $this->topup_order = $topup_order;
        $this->topUpGatewayFactory = $this->getTopUpGatewayFactory();
        $this->balance = $this->getBalance();
        $this->topUpGateway = $this->getGatewayModel();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $this->topUpGateway->update([
                'balance' => $this->balance
            ]);
            if($this->checkIfLessThanThreshold($this->topUpGateway, $this->balance)) {
                $this->sendSmsToGatewaySmsReceivers($this->topUpGateway, $this->balance);
            }
        }
    }

    private function getTopUpGatewayFactory()
    {
        $gateway_factory = new GatewayFactory();
        $gateway_factory->setGatewayName($this->topup_order->gateway)->setVendorId($this->topup_order->vendor_id);
        return $gateway_factory->get();
    }

    private function getGatewayModel()
    {
        return TopUpGateway::where('name', $this->topup_order->gateway)->first();
    }

    private function getBalance()
    {
        return $this->topUpGatewayFactory->getBalance();
    }

    private function checkIfLessThanThreshold($gateway, $balance)
    {
        $threshold = $gateway->threshold;
        return (double) $balance < (double) $threshold;
    }

    private function sendSmsToGatewaySmsReceivers($gateway, $balance)
    {
        $sms_receivers = $gateway->topupGatewaySmsReceivers;
        $message = "gateway balance ".$balance." which is less than threshold";
        $sms_receivers->each(function ($sms_receiver, $key) use ($message) {
            (new SmsHandler('top_up_threshold_notify'))->send($sms_receiver->phone, [
                'message' => $message
            ]);
        });
    }
}