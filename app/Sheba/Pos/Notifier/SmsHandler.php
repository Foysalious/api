<?php namespace Sheba\Pos\Notifier;

use App\Models\PosOrder;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use Sheba\PartnerWallet\PartnerTransactionHandler;

class SmsHandler
{
    /**
     * @var PosOrder
     */
    private $order;

    public function setOrder(PosOrder $order)
    {
        $this->order = $order->calculate();
        return $this;
    }

    public function handle()
    {
        $service_break_down = [];
        $this->order->items->each(function ($item) use (&$service_break_down) {
            $service_break_down[$item->id] = $item->service_name . '-' . $item->getTotal();
        });
        $service_break_down = implode(',', $service_break_down);

        $sms = (new SmsHandlerRepo('pos-order-details'))->send($this->order->customer->profile->mobile, [
            'order_id' => $this->order->id,
            'service_break_down' => $service_break_down,
            'total_amount' => $this->order->getNetBill(),
            'partner_name' => $this->order->partner->name
        ]);

        $sms_cost = $sms->getCost();
        $partner_transaction_handler = new PartnerTransactionHandler($this->order->partner);
        $partner_transaction_handler->debit($sms_cost, $sms_cost . " BDT has been deducted for sending pos order details sms (order id: {$this->order->id})", null, null);
    }
}