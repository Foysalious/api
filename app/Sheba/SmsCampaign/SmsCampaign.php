<?php namespace Sheba\SmsCampaign;

use App\Models\Partner;
use Sheba\Dal\SmsCampaignOrder\SmsCampaignOrder;
use Sheba\Dal\SmsCampaignOrderReceiver\SmsCampaignOrderReceiver;
use App\Models\Tag;
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

    private $smsHandler;
    private $mobileNumbers = [];
    private $customers;
    private $title;
    private $mobile;
    private $message;
    private $sms_count;
    /** @var Partner $partner */
    private $partner;

    public function __construct(SmsHandler $smsHandler)
    {
        $this->smsHandler = $smsHandler;
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
        $this->sms_count = $length > 160 ? ceil($length / 160) : 1;
        $this->setModifier($request['manager_resource']);
        return $this;
    }

    public function createOrder()
    {
        if (!$this->partnerHasEnoughBalance()) return false;

        $response = (object)$this->smsHandler->sendBulkMessages($this->mobileNumbers, $this->message);
        $campaign_order = SmsCampaignOrder::create($this->withBothModificationFields([
            'title' => $this->title,
            'message' => $this->message,
            'partner_id' => $this->partner->id,
            'rate_per_sms' => constants('SMS_CAMPAIGN.rate_per_sms'),
            'bulk_id' => isset($response->bulkId) ? $response->bulkId : null
        ]));
        $amount_to_be_deducted = 0.0;

        foreach ($response->messages as $index => $message) {
            $message = (object)$message;
            $amount_to_be_deducted += ($this->sms_count * constants('SMS_CAMPAIGN.rate_per_sms'));
            SmsCampaignOrderReceiver::create($this->withBothModificationFields([
                'sms_campaign_order_id' => $campaign_order->id,
                'receiver_number' => $message->to,
                'receiver_name' => $this->customers ? $this->customers[$index]['name'] : null,
                'message_id' => $message->messageId,
                'status' => Status::PENDING,
                'sms_count' => $this->sms_count
            ]));
        }

        $this->createTransactions($campaign_order, $amount_to_be_deducted);
        (new CampaignSmsStatusChanger())->processPendingSms();

        return true;
    }

    public function partnerHasEnoughBalance()
    {
        $amount_to_be_deducted_per_message = ($this->sms_count * constants('SMS_CAMPAIGN.rate_per_sms'));
        $total_amount_to_be_deducted = count($this->mobileNumbers) * $amount_to_be_deducted_per_message;
        return $this->partner->wallet >= $total_amount_to_be_deducted;
    }

    private function createTransactions(SmsCampaignOrder $campaign_order, $amount_to_be_deducted)
    {
        $this->createExpenseTrackerEntry($campaign_order, $amount_to_be_deducted);
        $log = $amount_to_be_deducted . "BDT. has been deducted for creating " . $this->title . ' sms campaign from your wallet.';
        $partner_transactions = (new WalletTransactionHandler())->setModel($this->partner)
            ->setSource(TransactionSources::SMS)->setType(Types::debit())
            ->setLog($log)->setAmount($amount_to_be_deducted)
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
