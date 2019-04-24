<?php namespace App\Transformers;

use App\Models\PosOrder;
use League\Fractal\TransformerAbstract;

class PosOrderTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['items'];

    public function transform(PosOrder $order)
    {
        $customer = $order->customer ? $order->customer->profile : null;
        return [
            'id' => $order->id,
            'created_by_name' => $order->created_by_name,
            'created_at' => $order->created_at->format('Y-m-d h:s:i A'),
            'partner_name' => $order->partner->name,
            'price' => (double)$order->getNetBill(),
            'payment_status' => $order->getPaymentStatus(),
            'vat' => (double)$order->getTotalVat(),
            'paid' => $order->getPaid(),
            'due' => $order->getDue(),
            'customer' => $customer ? collect([
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'image' => $customer->pro_pic,
            ]) : null,
            'payments' => $order->payments
        ];
    }

    public function includeItems($order)
    {
        $collection = $this->collection($order->items, new ItemTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}