<?php namespace Sheba\SmsCampaign;

use App\Models\Partner;
use App\Models\SmsCampaignOrder;
use App\Models\SmsCampaignOrderReceiver;
use App\Sheba\SmsCampaign\InfoBip\SmsHandler;
use Sheba\ModificationFields;

class SmsCampaign
{
    use ModificationFields;

    private $smsHandler;
    private $mobileNumbers = array();
    private $customers;
    private $title;
    private $message;
    private $sms_count;
    /** @var Partner $partner */
    private $partner;

    public function __construct(SmsHandler $smsHandler)
    {
        $this->smsHandler = $smsHandler;
    }

    public function formatRequest($request)
    {
        foreach ($request['customers'] as $customer) {
            array_push($this->mobileNumbers, formatMobile($customer['mobile']));
        }
        $this->customers = $request['customers'];
        $this->title = $request['title'];
        $this->message = $request['message'];
        $this->partner = $request['partner'];
        $length = mb_strlen($request['message'],'utf8');
        $this->sms_count = $length > 160 ? ceil($length / 160) : 1;
        $this->setModifier($request['manager_resource']);
        return $this;
    }
    
    public function createOrder()
    {
        if($this->partnerHasEnoughBalance()) {
            $response = (object) $this->smsHandler->sendBulkMessages($this->mobileNumbers, $this->message);
            $campaign_order_data = array(
                'title' => $this->title,
                'message' => $this->message,
                'partner_id' => $this->partner->id,
                'rate_per_sms' => constants('SMS_CAMPAIGN.rate_per_sms'),
                'bulk_id' => isset($response->bulkId) ? $response->bulkId : null
            );
            $campaign_order = SmsCampaignOrder::create($this->withBothModificationFields($campaign_order_data));
            $amount_to_be_deducted = 0.0;
            foreach($response->messages as $index => $message) {
                $message = (object) $message;
                $amount_to_be_deducted += ($this->sms_count * constants('SMS_CAMPAIGN.rate_per_sms'));
                $orderDetails = [
                    'sms_campaign_order_id' => $campaign_order->id,
                    'receiver_number' => $message->to,
                    'receiver_name' => $this->customers[$index]['name'],
                    'message_id' => $message->messageId,
                    'status' => constants('SMS_CAMPAIGN_RECEIVER_STATUSES.pending'),
                    'sms_count' => $this->sms_count
                ];
                SmsCampaignOrderReceiver::create($this->withBothModificationFields($orderDetails));
            }
            $this->partner->debitWallet($amount_to_be_deducted);
            $this->partner->walletTransaction(['amount' => $amount_to_be_deducted, 'type' => 'Debit', 'log' => $amount_to_be_deducted."BDT. has been deducted
                                            for creating ".$this->title.' sms campaign from your wallet.']);
            (new SmsLogs())->processLogs($this->smsHandler);
            return true;
        }
        return false;
    }

    public function partnerHasEnoughBalance()
    {
        $amount_to_be_deducted_per_message = ($this->sms_count * constants('SMS_CAMPAIGN.rate_per_sms'));
        $total_amount_to_be_deducted = count($this->mobileNumbers) * $amount_to_be_deducted_per_message;
        return $this->partner->wallet >= $total_amount_to_be_deducted ? true : false;
    }
}