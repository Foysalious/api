<?php namespace Sheba\Pos\Notifier;

use App\Models\Partner;
use App\Models\PosOrder;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\Pos\Order\Invoice\InvoiceService;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Expense\SmsPurchase;
use Sheba\AccountingEntry\Accounts\RootAccounts;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Exception;

class WebstoreOrderSmsHandler
{
    /**
     * @var PosOrder
     */
    private $order;

    public function setOrder(PosOrder $order) {
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

        $sms_handler = $this->buildSmsHandler();
        $sms_cost = $sms_handler->estimateCharge();
        if ((double)$partner->wallet < $sms_cost) return;
        //freeze money amount check
        WalletTransactionHandler::isDebitTransactionAllowed($partner, $sms_cost, 'এস-এম-এস পাঠানোর');

        $sms_handler->shoot();

        $transaction = (new WalletTransactionHandler())
            ->setModel($partner)
            ->setAmount($sms_cost)
            ->setType(Types::debit())
            ->setLog($sms_cost . " BDT has been deducted for sending pos order update sms to customer(order id: {$this->order->id})")
            ->setTransactionDetails([])
            ->setSource(TransactionSources::SMS)
            ->store();

        (new JournalCreateRepository())->setTypeId($partner->id)
            ->setSource($transaction)
            ->setAmount($sms_cost)
            ->setDebitAccountKey(SmsPurchase::SMS_PURCHASE_FROM_SHEBA)
            ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
            ->setDetails("Webstore sms cost")
            ->setReference($this->order->id)
            ->store();
    }

    /**
     * @return SmsHandlerRepo
     * @throws Exception
     */
    private function buildSmsHandler()
    {
        $message_data = [
            'order_id' => $this->order->partner_wise_order_id
        ];
        $invoice_link =   $this->order->invoice ? : $this->resolveInvoiceLink() ;

        if ($this->order->status == OrderStatuses::PROCESSING) {
            $sms_handler = (new SmsHandlerRepo('pos-order-accept-customer'));
        } elseif ($this->order->status == OrderStatuses::CANCELLED || $this->order->status == OrderStatuses::DECLINED) {
            $sms_handler = (new SmsHandlerRepo('pos-order-cancelled-customer'));
        } elseif ($this->order->status == OrderStatuses::SHIPPED) {
            $sms_handler = (new SmsHandlerRepo('pos-order-shipped-customer'));
        } elseif ($this->order->status == OrderStatuses::COMPLETED) {
            $sms_handler = (new SmsHandlerRepo('pos-order-delivered-customer'));
        } else {
            $sms_handler = (new SmsHandlerRepo('pos-order-place-customer'));
            $message_data += [
                'net_bill' => $this->order->getNetBill(),
                'payment_status' => $this->order->getPaid() ? 'প্রদত্ত' : 'বকেয়া',
                'store_name' => $this->order->partner->name,
                'invoice_link' => $invoice_link
            ];
        }

        return $sms_handler
            ->setMobile($this->order->customer->profile->mobile)
            ->setFeatureType(FeatureType::WEB_STORE)
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
