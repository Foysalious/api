<?php namespace Sheba\SmsCampaign;

use App\Models\Partner;
use App\Models\Tag;
use Sheba\PartnerWallet\PartnerTransactionHandler;

class Refund
{
    /** @var Partner $refundReceiver */
    private $refundReceiver;
    private $smsCount;
    private $transaction;

    /**
     * @param $refund_receiver
     * @return $this
     */
    public function setRefundReceiver(Partner $refund_receiver)
    {
        $this->refundReceiver = $refund_receiver;
        return $this;
    }

    public function setNumberOfSms($sms_count)
    {
        $this->smsCount = $sms_count;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function adjustWallet()
    {
        $handler = new PartnerTransactionHandler($this->refundReceiver);

        $amount = $this->smsCount * constants('SMS_CAMPAIGN.rate_per_sms');
        $log = $amount . " BDT has been credited for failing to sent a message in a campaign you have created.";
        $tag = Tag::where('name', 'refunded sms campaign')->pluck('id')->toArray();

        $this->transaction = $handler->credit($amount, $log, null, $tag);
    }

    public function getTransaction(){
        return $this->transaction;
    }
}