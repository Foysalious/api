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
            'customer_name' => $order->customer->profile->name,
            'created_at' => $order->created_at->format('Y-m-d h:i A'),
            'price' => (double)$order->getNetBill(),
            'status' => $order->status
        ];
    }


}