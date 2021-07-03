<?php namespace App\Transformers;

use App\Models\PosOrder;
use App\Sheba\Partner\Delivery\Methods;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInformation;
use Sheba\PaymentLink\PaymentLinkTransformer;

class PosOrderTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['items', 'customer', 'payments', 'return_orders'];

    /**
     * @param PosOrder $order
     * @return array
     */
    public function transform(PosOrder $order)
    {
        $refundable = $order->isRefundable();
        $refund_status = $order->getRefundStatus();
        $data = [
            'id' => $order->id,
            'previous_order_id' => $order->previous_order_id,
            'note' => $order->note,
            'created_by_name' => $order->created_by_name,
            'created_at' => $order->created_at->format('Y-m-d h:i A'),
            'date'=>$order->created_at->format('Y-m-d'),
            'partner_name' => $order->partner->name,
            'price' => (double)$order->getNetBill(),
            'original_price' => (double)$order->getTotalBill(),
            'order_status' => $order->status,
            'payment_status' => $order->getPaymentStatus(),
            'vat' => (double)$order->getTotalVat(),
            'discount_amount' => (double)$order->getTotalDiscount(),
            'paid' => $order->getPaid(),
            'status'=> $order->getPaymentStatus(),
            'due' => $order->getDue(),
            'customer' => null,
            'address' => $order->address,
            'is_refundable' => $refundable && empty($refund_status),
            'refund_status' => $refund_status,
            'return_orders' => null,
            'partner_wise_order_id' => $order->partner_wise_order_id,
            'partner_wise_previous_order_id' => $order->previousOrder ? $order->previousOrder->partner_wise_order_id : null,
            'sales_channel' => $order->sales_channel,
            'delivery_charge' => $order->delivery_charge,
            'delivery_by_third_party' => $order->delivery_thana && $order->delivery_district ? 1 : 0,
            'selected_delivery_method' => $this->getDeliveryMethod($order->partner_id),
            'total_weight' => $order->weight
        ];

        if ($data['due'] > 0) $data['payment_link_target'] = $order->getPaymentLinkTarget();

        return $data;
    }

    private function getDeliveryMethod($partner_id)
    {
        $partnerDeliveryInformation =  PartnerDeliveryInformation::where('partner_id', $partner_id)->first();
        return !empty($partnerDeliveryInformation) ? $partnerDeliveryInformation->delivery_vendor : Methods::OWN_DELIVERY;
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
        if ($order->customer) $order->customer['partner_id'] = $order->partner_id;
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

    public function addPaymentLinkDataToOrder(&$order, PaymentLinkTransformer $payment_link)
    {
        $order['payment_link'] = [
            'id' => $payment_link->getLinkID(),
            'status' => $payment_link->getIsActive() ? 'active' : 'inactive',
            'link' => $payment_link->getLink(),
            'amount' => $payment_link->getAmount(),
            'created_at' => $payment_link->getCreatedAt()->format('d-m-Y h:s A')
        ];
    }
}
