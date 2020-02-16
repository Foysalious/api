<?php namespace App\Transformers;

use App\Models\PosOrder;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;

class PosOrderTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['items', 'customer', 'payments', 'return_orders'];

    /**
     * @param PosOrder $order
     * @return array
     */
    public function transform(PosOrder $order)
    {
        $refundable=$order->isRefundable();
        $refund_status=$order->getRefundStatus();
        $data = [
            'id' => $order->id,
            'previous_order_id' => $order->previous_order_id,
            'note' => $order->note,
            'created_by_name' => $order->created_by_name,
            'created_at' => $order->created_at->format('Y-m-d h:i A'),
            'partner_name' => $order->partner->name,
            'price' => (double)$order->getNetBill(),
            'payment_status' => $order->getPaymentStatus(),
            'vat' => (double)$order->getTotalVat(),
            'discount_amount' => (double)$order->getTotalDiscount(),
            'paid' => $order->getPaid(),
            'due' => $order->getDue(),
            'customer' => null,
            'is_refundable' =>$refundable&&empty($refund_status) ,
            'refund_status' => $refund_status,
            'return_orders' => null,
            'partner_wise_order_id' => $order->partner_wise_order_id,
            'partner_wise_previous_order_id' => $order->previousOrder ? $order->previousOrder->partner_wise_order_id : null
        ];
        if ($data['due'] > 0) {
            $repo = app(PaymentLinkRepositoryInterface::class);
            $response = $repo->getPaymentLinkByTargetIdType($data['id']);
            if ($response['code'] == 200) {
                $details = $response['links'][0];
                $data['payment_link'] = [
                    'id' => $details['linkId'],
                    'status' => $details['isActive'] ? 'active' : 'inactive',
                    'link' => $details['link'],
                    'amount' => $details['amount'],
                    'created_at' => date('d-m-Y h:s A', $details['createdAt'] / 1000)
                ];

            }
        }

        return $data;
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

    /**
     * @param $order
     * @return \Illuminate\Support\Collection|Collection|Item
     */
    public function includeReturnOrders($order)
    {
        if ($order->id <= (int)config('pos.last_returned_order_for_v1')) {
            return $this->item(null, function () {
                return [];
            });
        }
        $collection = $this->collection($order->refundLogs()->get(), new PosOrderReturnedTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}
