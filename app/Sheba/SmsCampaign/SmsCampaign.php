<?php namespace Sheba\SmsCampaign;

use App\Models\Partner;
use ReflectionException;
use Sheba\AccountingEntry\Accounts\Accounts;

use Sheba\AccountingEntry\Accounts\RootAccounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\AccountingEntry\Exceptions\InvalidSourceException;
use Sheba\AccountingEntry\Exceptions\KeyNotFoundException;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\Dal\SmsCampaignOrder\SmsCampaignOrder;
use Sheba\Dal\SmsCampaignOrder\SmsCampaignOrderRepository;
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
    /** @var Partner $partner */
    private $partner;
    private $transaction;

    public function __construct(SmsHandler $sms, CampaignSmsStatusChanger $sms_status_changer,
        SmsCampaignOrderReceiverRepository $receiver_repo, SmsCampaignOrderRepository $order_repo)
    {
        $this->smsHandler = $sms;
        $this->smsStatusChanger = $sms_status_changer;
        $this->orderRepo = $order_repo;
        $this->receiverRepo = $receiver_repo;
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
        // TODO
        // $length = mb_strlen($request['message'], 'utf8');
        // $this->smsCount = $length > self::SMS_MAX_LENGTH ? ceil($length / self::SMS_MAX_LENGTH) : 1;
        return $this;
    }

    /**
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException
     * @throws ReflectionException
     * @throws KeyNotFoundException
     */
    public function createOrder()
    {
        if (!$this->partnerHasEnoughBalance()) return false;

        $response = $this->smsHandler->sendBulkMessages($this->mobileNumbers, $this->message);
        $campaign_order = $this->orderRepo->create([
                                                       'title' => $this->title,
                                                       'message' => $this->message,
                                                       'partner_id' => $this->partner->id,
                                                       'rate_per_sms' => $response->getChargePerSms(),
                                                       'bulk_id' => $response->getSmsId() ?: null
                                                   ]);

        foreach ($response->getSinglesResponse() as $index => $single) {
            $this->receiverRepo->create([
                                            'sms_campaign_order_id' => $campaign_order->id,
                                            'receiver_number' => $single->getMobile(),
                                            'receiver_name' => $this->getNthCustomerName($index),
                                            'message_id' => $single->getSmsId(),
                                            'status' => Status::PENDING,
                                            'sms_count' => $single->getSmsCount()
                                        ]);
        }

        $amount_to_be_deducted = $response->getTotalCharge();
        $this->storeJournal($amount_to_be_deducted, $campaign_order->id);
        $this->createTransactions($campaign_order, $amount_to_be_deducted);
        $this->smsStatusChanger->processPendingSms();

        return true;
    }

    private function getNthCustomerName($n)
    {
        return $this->customers && $this->customers[$n] && $this->customers[$n]['name'] ?
            $this->customers[$n]['name'] :
            null;
    }

    public function partnerHasEnoughBalance()
    {
        $charge = $this->smsHandler->getBulkCharge($this->mobileNumbers, $this->message);
        return $this->partner->wallet >= $charge;
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
        $this->transaction = $partner_transactions;
    }

    /**
     * @param $cost
     * @param $campaignOrderId
     * @throws ReflectionException
     * @throws AccountingEntryServerError
     * @throws InvalidSourceException
     * @throws KeyNotFoundException
     */
    public function storeJournal($cost, $campaignOrderId){
        if ($this->transaction){
            (new JournalCreateRepository())->setTypeId($this->partner->id)
                ->setSource($this->transaction)
                ->setAmount($cost)
                ->setDebitAccountKey((new Accounts())->expense->sms_purchase::SMS_PURCHASE_FROM_SHEBA)
                ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
                ->setDetails("SMS marketing")
                ->setReference($campaignOrderId)
                ->store();
        }
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
