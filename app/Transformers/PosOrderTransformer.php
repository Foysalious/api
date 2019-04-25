<?php namespace App\Transformers;

use App\Models\PosOrder;
use League\Fractal\TransformerAbstract;

class PosOrderTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['items', 'customer', 'payments'];

    public function transform(PosOrder $order)
    {
        return [
            'id' => $order->id,
            'previous_order_id' => $order->previous_order_id,
            'created_by_name' => $order->created_by_name,
            'created_at' => $order->created_at->format('Y-m-d h:s:i A'),
            'partner_name' => $order->partner->name,
            'price' => (double)$order->getNetBill(),
            'payment_status' => $order->getPaymentStatus(),
            'vat' => (double)$order->getTotalVat(),
            'paid' => $order->getPaid(),
            'due' => $order->getDue(),
            'customer' => null,
            'is_refundable' => $order->items()->whereNull('service_id')->first() ? false : true,
            'refund_status' => $order->getRefundStatus()
        ];
    }

    public function includeItems($order)
    {
        $collection = $this->collection($order->items, new ItemTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    public function includePayments($order)
    {
        $collection = $this->collection($order->payments, new PosOrderPaymentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    public function includeCustomer($order)
    {
        $collection = $this->item($order->customer, new PosCustomerTransformer());
        return $collection->getData() ? $collection : null;
    }
}