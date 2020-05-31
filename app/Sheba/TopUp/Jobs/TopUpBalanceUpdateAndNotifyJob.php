<?php namespace Sheba\TopUp\Jobs;

use App\Jobs\Job;
use App\Models\TopUpOrder;
use App\Repositories\SmsHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Dal\TopUpGateway\Model as TopUpGateway;

class TopUpBalanceUpdateAndNotifyJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var TopUpGateway */
    protected $topUpGateway;
    protected $balance;

    public function __construct(TopUpOrder $topup_order, $vendor)
    {
        $this->balance = $vendor->getBalance();
        $this->topUpGateway = $this->getGatewayModel($topup_order->gateway);
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            if($this->checkIfLessThanThreshold($this->topUpGateway, $this->balance)) {
                $this->sendSmsToGatewaySmsReceivers($this->topUpGateway, $this->balance);
            }
            $this->topUpGateway->update([
                'balance' => $this->balance
            ]);
        }
    }

    private function getGatewayModel($gateway)
    {
        return TopUpGateway::where('name', $gateway)->first();
    }

    private function checkIfLessThanThreshold($gateway, $balance)
    {
        $threshold = $gateway->threshold;
        dd($gateway, $balance, $threshold);
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