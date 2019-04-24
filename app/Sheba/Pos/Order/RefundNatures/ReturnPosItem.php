<?php namespace Sheba\Pos\Order\RefundNatures;

use Sheba\Pos\Log\Creator as LogCreator;
use Sheba\Pos\Order\Updater;
use Sheba\Pos\Payment\Creator as PaymentCreator;

abstract class ReturnPosItem extends RefundNature
{
    protected $details;
    private $old_services;
    /** @var PaymentCreator $paymentCreator */
    protected $paymentCreator;

    public function __construct(LogCreator $log_creator, Updater $updater, PaymentCreator $payment_creator)
    {
        parent::__construct($log_creator, $updater);
        $this->paymentCreator = $payment_creator;
    }

    public function update()
    {
        $this->old_services = $this->order->items->pluck('quantity', 'service_id')->toArray();
        $this->updater->setOrder($this->order)->setData($this->data)->update();

        $this->refundPayment();
        $this->generateDetails();
        $this->saveLog();
    }

    /**
     * GENERATE LOG DETAILS DATA
     */
    private function generateDetails()
    {
        $changes = [];
        $this->services->each(function ($service) use (&$changes) {
            $changes[$service->id]['qty'] = [
                'new' => (double)$service->quantity,
                'old' => (double)$this->old_services[$service->id],
            ];
        });
        $details['items']['changes'] = $changes;
        $this->details = json_encode($details);
    }

    private function refundPayment()
    {
        $payment_data['pos_order_id'] = $this->order->id;
        $payment_data['amount'] = $this->data['refund_amount'];
        $payment_data['method'] = $this->data['payment_method'];

        $this->paymentCreator->debit($payment_data);
    }
}