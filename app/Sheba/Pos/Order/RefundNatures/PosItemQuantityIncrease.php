<?php namespace Sheba\Pos\Order\RefundNatures;

use Sheba\Pos\Log\Supported\Types;

class PosItemQuantityIncrease extends ReturnPosItem
{
    public function update()
    {
        $this->old_services = $this->new ? $this->order->items->pluckMultiple([
            'quantity',
            'unit_price'
        ], 'id', true)->toArray() : $this->old_services = $this->order->items->pluckMultiple([
            'quantity',
            'unit_price'
        ], 'service_id')->toArray();
        $this->updater->setOrder($this->order)->setData($this->data)->setNew($this->new)->update();
        $this->refundPayment();
        $this->generateDetails();
        $this->saveLog();
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
    }

    protected function saveLog()
    {
        $this->logCreator->setOrder($this->order)->setType(Types::ITEM_QUANTITY_INCREASE)->setLog("Order item added, order id: {$this->order->id}")->setDetails($this->details)->create();
    }


}
