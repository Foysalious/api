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
use Sheba\TopUp\Gateway\Names;
use Sheba\TopUp\Vendor\Internal\SslClient;

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
    protected $sslClient;

    public function __construct(TopUpOrder $topup_order, $response)
    {
        $this->topup_order = $topup_order;
        $this->topUpGateway = $this->getGatewayModel();
        $this->response = $response;
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            if ($this->topup_order->gateway == Names::SSL) {
                $this->sslClient = app(SslClient::class);
                $this->balance = $this->sslClient->getBalance()->available_credit;
            } else {
                $this->balance = $this->getBalance();
            }

            $this->topUpGateway->update([
                'balance' => $this->balance
            ]);
            if($this->checkIfLessThanThreshold($this->topUpGateway, $this->balance)) {
                $this->sendSmsToGatewaySmsReceivers($this->topUpGateway, $this->balance);
            }
        }
    }

    private function getGatewayModel()
    {
        return TopUpGateway::where('name', $this->topup_order->gateway)->first();
    }

    private function getBalance()
    {
        if ($this->topup_order->gateway == Names::ROBI || $this->topup_order->gateway == Names::AIRTEL || $this->topup_order->gateway == Names::BANGLALINK) {
            return $this->parseBalanceFromResponseMessage($this->response);
        } else {
            return $this->topUpGateway->balance;
        }
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

    private function parseBalanceFromResponseMessage($message)
    {
        $str = substr($message, strpos($message, 'balance') + 7);
        return (int) filter_var($str, FILTER_SANITIZE_NUMBER_INT);
    }
}