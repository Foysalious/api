<?php namespace Sheba\TopUp\Jobs;

use App\Jobs\Job;
use App\Models\TopUpOrder;
use App\Repositories\SmsHandler;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Dal\TopUpGateway\Model as TopUpGateway;
use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Gateway\Names;
use Sheba\TopUp\Gateway\Ssl;
use Sheba\TopUp\Vendor\VendorFactory;

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
    protected $response;

    public function __construct(TopUpOrder $topup_order, $response)
    {
        $this->topup_order = $topup_order;
        $this->topUpGateway = $this->getGatewayModel();
        $this->response = $response;
    }

    public function handle(Ssl $ssl)
    {
        if ($this->attempts() >= 2 || $this->isMock()) return;

        $this->balance = $this->isSSL() ? $ssl->getBalance()->available_credit : $this->getBalance();
        $this->topUpGateway->update([
            'balance' => $this->balance
        ]);

        if($this->isAboveThreshold($this->topUpGateway, $this->balance)) return;

        $this->sendSmsToGatewaySmsReceivers($this->topUpGateway, $this->balance);
    }

    private function getGatewayModel()
    {
        return TopUpGateway::where('name', $this->topup_order->gateway)->first();
    }

    private function getBalance()
    {
        return $this->isPretups() ? $this->parseBalanceFromResponseMessage($this->response) : $this->topUpGateway->balance;
    }

    private function isAboveThreshold($gateway, $balance)
    {
        return !$this->checkIfLessThanThreshold($gateway, $balance);
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
            (new SmsHandler('top_up_threshold_notify'))
                ->setBusinessType(BusinessType::BONDHU)
                ->setFeatureType(FeatureType::TOP_UP)
                ->send($sms_receiver->phone, [
                    'message' => $message
                ]);
        });
    }

    private function parseBalanceFromResponseMessage($message)
    {
        $str = substr($message, strpos($message, 'balance') + 7);
        return (int) filter_var($str, FILTER_SANITIZE_NUMBER_INT);
    }

    private function isMock()
    {
        return $this->topup_order->vendor_id == VendorFactory::MOCK;
    }

    private function isSSL()
    {
        return $this->topup_order->gateway == Names::SSL;
    }

    private function isPretups()
    {
        return $this->topup_order->gateway == Names::ROBI ||
            $this->topup_order->gateway == Names::AIRTEL ||
            $this->topup_order->gateway == Names::BANGLALINK;
    }
}
