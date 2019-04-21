<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class PosOrderTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['items'];

    public function transform($order)
    {
        return [
            'id' => $order->id,
            'created_by_name' => $order->created_by_name,
            'created_at' => $order->created_at->format('Y-m-d h:s:i A'),
            'partner_name' => $order->partner->name,
            'price' => (double)$order->netBill,
            'payment_status' => $order->paymentStatus,
            'vat' => (double)$order->totalVat,
            'paid' => $order->paid,
            'due' => $order->due
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