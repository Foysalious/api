<?php namespace Sheba\SmsCampaign;

use App\Models\Partner;
use Sheba\Dal\SmsCampaignOrder\SmsCampaignOrder;
use Sheba\Dal\SmsCampaignOrder\SmsCampaignOrderRepository;
use Sheba\Dal\SmsCampaignOrderReceiver\SmsCampaignOrderReceiver;
use App\Models\Tag;
use Sheba\Dal\SmsCampaignOrderReceiver\SmsCampaignOrderReceiverRepository;
use Sheba\Dal\SmsCampaignOrderReceiver\Status;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class SmsCampaign
{
    use ModificationFields;

    const SMS_MAX_LENGTH = 160;

    /** @var SmsHandler */
    private $smsHandler;
    /** @var SmsHandler */
    private $smsStatusChanger;
    /** @var SmsCampaignOrderReceiverRepository */
    private $receiverRepo;
    /** @var SmsCampaignOrderRepository */
    private $orderRepo;
    private $mobileNumbers = [];
    private $customers;
    private $title;
    private $mobile;
    private $message;
    private $smsCount;
    private $ratePerSms;
    /** @var Partner $partner */
    private $partner;

    public function __construct(SmsHandler $sms, CampaignSmsStatusChanger $sms_status_changer,
                                SmsCampaignOrderReceiverRepository $receiver_repo, SmsCampaignOrderRepository $order_repo)
    {
        $this->smsHandler = $sms;
        $this->smsStatusChanger = $sms_status_changer;
        $this->orderRepo = $order_repo;
        $this->receiverRepo = $receiver_repo;
        $this->ratePerSms = constants('SMS_CAMPAIGN.rate_per_sms');
    }

    public function setMobile($mobile)
    {
        $this->mobile = formatMobile($mobile);
        return $this;
    }

    public function pushMobileNumber()
    {
        array_push($this->mobileNumbers, $this->mobile);
        return $this;
    }

    public function formatRequest($request)
    {
        if (!isset($request['file'])) {
            foreach ($request['customers'] as $customer) {
                array_push($this->mobileNumbers, formatMobile($customer['mobile']));
            }
            $this->customers = $request['customers'];
        }
        $this->title = $request['title'];
        $this->message = $request['message'];
        $this->partner = $request['partner'];
        $length = mb_strlen($request['message'], 'utf8');
        $this->smsCount = $length > self::SMS_MAX_LENGTH ? ceil($length / self::SMS_MAX_LENGTH) : 1;
        return $this;
    }

    public function createOrder()
    {
        if (!$this->partnerHasEnoughBalance()) return false;

        $response = (object)$this->smsHandler->sendBulkMessages($this->mobileNumbers, $this->message);
        $campaign_order = $this->orderRepo->create([
            'title' => $this->title,
            'message' => $this->message,
            'partner_id' => $this->partner->id,
            'rate_per_sms' => $this->ratePerSms,
            'bulk_id' => isset($response->bulkId) ? $response->bulkId : null
        ]);
        $amount_to_be_deducted = 0.0;

        foreach ($response->messages as $index => $message) {
            $message = (object)$message;
            $amount_to_be_deducted += $this->getSingleSmsCost();
            $this->receiverRepo->create([
                'sms_campaign_order_id' => $campaign_order->id,
                'receiver_number' => $message->to,
                'receiver_name' => $this->customers && $this->customers[$index] && $this->customers[$index]['name'] ? $this->customers[$index]['name'] : null,
                'message_id' => $message->messageId,
                'status' => Status::PENDING,
                'sms_count' => $this->smsCount
            ]);
        }

        $this->createTransactions($campaign_order, $amount_to_be_deducted);
        $this->smsStatusChanger->processPendingSms();

        return true;
    }

    public function partnerHasEnoughBalance()
    {
        return $this->partner->wallet >= $this->getOrderCost();
    }

    private function getOrderCost()
    {
        return count($this->mobileNumbers) * $this->getSingleSmsCost();
    }

    private function getSingleSmsCost()
    {
        return $this->smsCount * $this->ratePerSms;
    }

    private function createTransactions(SmsCampaignOrder $campaign_order, $cost)
    {
        $this->createExpenseTrackerEntry($campaign_order, $cost);
        $log = $cost . "BDT. has been deducted for creating " . $this->title . ' sms campaign from your wallet.';
        $partner_transactions = (new WalletTransactionHandler())->setModel($this->partner)
            ->setSource(TransactionSources::SMS)->setType(Types::debit())
            ->setLog($log)->setAmount($cost)
            ->store();
        $tag = Tag::where('name', 'credited sms campaign')->pluck('id')->toArray();
        $partner_transactions->tags()->sync($tag);
    }

    private function createExpenseTrackerEntry(SmsCampaignOrder $campaign_order, $amount_to_be_deducted)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry = app(AutomaticEntryRepository::class);
        $entry->setAmount($amount_to_be_deducted)
            ->setPartner($this->partner)
            ->setHead(AutomaticExpense::SMS)
            ->setSourceType(class_basename($campaign_order))
            ->setSourceId($campaign_order->id)
            ->store();
    }
}
