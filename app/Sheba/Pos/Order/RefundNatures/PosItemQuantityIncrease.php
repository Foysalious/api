<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;
use Exception;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\Pos\Log\Supported\Types;

class PosItemQuantityIncrease extends ReturnPosItem
{
    protected $refundAmount = 0;

    public function update()
    {
        try {
            $this->old_services = $this->new ? $this->order->items->pluckMultiple([
                'quantity',
                'unit_price'
            ], 'id', true)->toArray() : $this->old_services = $this->order->items->pluckMultiple([
                'quantity',
                'unit_price'
            ], 'service_id')->toArray();

            $this->makeInventoryProduct($this->order->items, $this->data['services']);
            $this->updater->setOrder($this->order)->setData($this->data)->setNew($this->new)->update();
            $this->refundPayment();
            $this->generateDetails($this->order);
            $this->saveLog();
            if ($this->order) {
                $this->updateEntry($this->order, 'quantity_increase');
                $this->updateIncome($this->order);
            }
        } catch (ExpenseTrackingServerError $e) {
            app('sentry')->captureException($e);
        }
    }
    private function refundPayment()
    {
        $payment_data['pos_order_id'] = $this->order->id;
        $payment_data['amount']       = $this->data['paid_amount'];
        $payment_data['method']       = $this->data['payment_method'];
        if ($this->data['paid_amount'] > 0) {
            $this->paymentCreator->credit($payment_data);
        } else {
            $payment_data['amount'] = abs($payment_data['amount']);
            $this->paymentCreator->debit($payment_data);
        }
        $this->refundAmount = $payment_data['amount'];
    }

    protected function saveLog()
    {
        $this->logCreator->setOrder($this->order)->setType(Types::ITEM_QUANTITY_INCREASE)->setLog("Order item added, order id: {$this->order->id}")->setDetails($this->details)->create();
    }

    /**
     * @param PosOrder $order
     * @throws ExpenseTrackingServerError
     */
    private function updateIncome(PosOrder $order)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry  = app(AutomaticEntryRepository::class);
        $amount = (double)$order->calculate()->getNetBill();
        $entry->setPartner($order->partner)->setAmount($amount)->setAmountCleared($order->getPaid())->setHead(AutomaticIncomes::POS)->setSourceType(class_basename($order))->setSourceId($order->id)->updateFromSrc();
    }
}
