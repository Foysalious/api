<?php namespace Sheba\Pos\Notifier;

use App\Models\Partner;
use App\Models\PosOrder;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
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
    public function handle() {
        /** @var Partner $partner */
        $partner = $this->order->partner;
        $partner->reload();
        if (empty($this->order->customer)) return;
        $sms = $this->getSms();
        $sms_cost = $sms->getCost();
        if ((double)$partner->wallet > (double)$sms_cost) {
            /** @var WalletTransactionHandler $walletTransactionHandler */
            $sms->setFeatureType(FeatureType::WEB_STORE)->setBusinessType(BusinessType::SMANAGER)->shoot();
            $transaction = (new WalletTransactionHandler())->setModel($partner)->setAmount($sms_cost)->setType(Types::debit())->setLog($sms_cost . " BDT has been deducted for sending pos order update sms to customer(order id: {$this->order->id})")->setTransactionDetails([])->setSource(TransactionSources::SMS)->store();

            (new JournalCreateRepository())->setTypeId($partner->id)
                ->setSource($transaction)
                ->setAmount($sms_cost)
                ->setDebitAccountKey(SmsPurchase::SMS_PURCHASE_FROM_SHEBA)
                ->setCreditAccountKey((new Accounts())->asset->sheba::SHEBA_ACCOUNT)
                ->setDetails("Webstore sms cost")
                ->setReference($this->order->id)
                ->store();
        }

    }

    /**
     * @return SmsHandlerRepo
     * @throws Exception
     */
    private function getSms() {
        if ($this->order->status == OrderStatuses::PROCESSING) {
            $sms = (new SmsHandlerRepo('pos-order-accept-customer'))->setVendor('adareach')->setMobile($this->order->customer->profile->mobile)->setMessage(['order_id' => $this->order->partner_wise_order_id]);
        } elseif ($this->order->status == OrderStatuses::CANCELLED || $this->order->status == OrderStatuses::DECLINED) {
            $sms = (new SmsHandlerRepo('pos-order-cancelled-customer'))->setVendor('adareach')->setMobile($this->order->customer->profile->mobile)->setMessage(['order_id' => $this->order->partner_wise_order_id]);
        } elseif ($this->order->status == OrderStatuses::SHIPPED) {
            $sms = (new SmsHandlerRepo('pos-order-shipped-customer'))->setVendor('adareach')->setMobile($this->order->customer->profile->mobile)->setMessage(['order_id' => $this->order->partner_wise_order_id]);
        } elseif ($this->order->status == OrderStatuses::COMPLETED) {
            $sms = (new SmsHandlerRepo('pos-order-delivered-customer'))->setVendor('adareach')->setMobile($this->order->customer->profile->mobile)->setMessage(['order_id' => $this->order->partner_wise_order_id]);
        } else {
            $sms = (new SmsHandlerRepo('pos-order-place-customer'))->setVendor('adareach')->setMobile($this->order->customer->profile->mobile)->setMessage([
                'order_id' => $this->order->partner_wise_order_id,
                'net_bill' => $this->order->getNetBill(),
                'payment_status' => $this->order->getPaid() ? 'প্রদত্ত' : 'বকেয়া'
            ]);
        }
        return $sms;
    }
}