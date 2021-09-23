<?php namespace Sheba\Pos\Notifier;

use App\Models\Partner;
use App\Models\PosOrder;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\Pos\Order\Invoice\InvoiceService;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Exception;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SmsPurchase;
use Sheba\AccountingEntry\Accounts\RootAccounts;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class SmsHandler
{
    /** @var PosOrder */
    private $order;

    public function setOrder(PosOrder $order)
    {
        $this->order = $order->calculate();
        return $this;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        /** @var Partner $partner */
        $partner = $this->order->partner;
        $partner->reload();
        if (empty($this->order->customer)) return;

        $service_break_down = [];
        $this->order->items->each(function ($item) use (&$service_break_down) {
            $service_break_down[$item->id] = $item->service_name . ': ' . $item->getTotal();
        });

        $service_break_down = implode(',', $service_break_down);
        $sms                = $this->getSms($service_break_down);
        $sms_cost           = $sms->getCost();
        if ((double)$partner->wallet > (double)$sms_cost) {
            /** @var WalletTransactionHandler $walletTransactionHandler */
            try{
                $sms->setBusinessType(BusinessType::SMANAGER)
                    ->setFeatureType(FeatureType::POS)
                    ->shoot();
            }catch(\Throwable $e)
            {
            }

            $transaction = (new WalletTransactionHandler())->setModel($partner)->setAmount($sms_cost)->setType(Types::debit())->setLog($sms_cost . " BDT has been deducted for sending pos order details sms (order id: {$this->order->id})")->setTransactionDetails([])->setSource(TransactionSources::SMS)->store();
            $this->storeJournal($partner, $transaction);
        }

    }


    private function storeJournal($partner, $transaction) {
        (new JournalCreateRepository())->setTypeId($partner->id)
            ->setSource($transaction)
            ->setAmount($transaction->amount)
            ->setDebitAccountKey(SmsPurchase::SMS_PURCHASE_FROM_SHEBA)
            ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setDetails("Pos sms sent charge")
            ->setReference("")
            ->store();
    }

    /**
     * @return SmsHandlerRepo
     * @throws Exception
     */
    private function getSms()
    {
        $invoice_link =   $this->order->invoice ? : $this->resolveInvoiceLink() ;
        $message_data = [
            'order_id'           => $this->order->partner_wise_order_id,
            'total_amount'       => $this->order->getNetBill(),
            'partner_name'       => $this->order->partner->name,
            'invoice_link'       => $invoice_link
        ];

        if ($this->order->getDue() > 0) {
            $sms_handler = (new SmsHandlerRepo('pos-due-order-bills'));
            $message_data['total_due_amount'] = $this->order->getDue();
        } else {
            $sms_handler = (new SmsHandlerRepo('pos-order-bills'));
        }

        return $sms_handler
            ->setMobile($this->order->customer->profile->mobile)
            ->setFeatureType(FeatureType::POS)
            ->setBusinessType(BusinessType::SMANAGER)
            ->setMessage($message_data);
    }

    /**
     * @throws NotAssociativeArray
     */
    private function resolveInvoiceLink()
    {
        /** @var InvoiceService $invoiceService */
        $invoiceService = app(InvoiceService::class)->setPosOrder($this->order);
        return $invoiceService->generateInvoice()->saveInvoiceLink()->getInvoiceLink();
    }
}
