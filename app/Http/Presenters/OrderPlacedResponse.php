<?php namespace App\Http\Presenters;


use App\Models\Order;
use App\Models\Payment;

class OrderPlacedResponse extends Presenter
{
    /** @var Order */
    private $order;
    /** @var Payment */
    private $payment;

    public function __construct(Order $order, Payment $payment = null)
    {
        $this->order = $order;
        $this->payment = $payment;
    }

    public function toArray()
    {
        $payment = $this->payment ? $this->payment->getFormattedPayment() : null;
        $job = $this->order->jobs->first();
        $partner_order = $job->partnerOrder;
        $order_with_response_data = [
            'job_id' => $job->id,
            'order_code' => $this->order->code(),
            'payment' => $payment,
            'order' => [
                'id' => $this->order->id,
                'code' => $this->order->code(),
                'job' => ['id' => $job->id]
            ]
        ];

        if ($partner_order->partner_id) {
            $order_with_response_data['provider_mobile'] = $partner_order->partner->getContactNumber();
        }

        return $order_with_response_data;
    }
}
