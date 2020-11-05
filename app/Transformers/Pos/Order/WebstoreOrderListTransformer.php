<?php namespace App\Transformers\Pos\Order;


use App\Models\PosOrder;
use League\Fractal\TransformerAbstract;

class WebstoreOrderListTransformer extends TransformerAbstract
{
    public function transform(PosOrder $order)
    {
        $order->calculate();
        return [
            'id' => $order->id,
            'customer_name' => $order->customer && $order->customer->profile ? $order->customer->profile->name : null,
            'created_at' => $order->created_at->format('Y-m-d h:i A'),
            'price' => (double)$order->getNetBill(),
            'status' => $order->status
        ];
    }


}