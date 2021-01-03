<?php namespace App\Transformers\Pos\Order;


use App\Models\PartnerPosCustomer;
use App\Models\PosOrder;
use League\Fractal\TransformerAbstract;

class WebstoreOrderListTransformer extends TransformerAbstract
{
    public function transform(PosOrder $order)
    {
        $order->calculate();
        return [
            'id' => $order->id,
            'customer_name' => $order->customer ? PartnerPosCustomer::getPartnerPosCustomerName($order->partner_id, $order->customer->id) : null,
            'created_at' => $order->created_at->format('Y-m-d h:i A'),
            'created_at_date_time' => $order->created_at->toDateTimeString(),
            'price' => (double)$order->getNetBill(),
            'status' => $order->status,
            'partner_wise_order_id' => $order->partner_wise_order_id
        ];
    }


}