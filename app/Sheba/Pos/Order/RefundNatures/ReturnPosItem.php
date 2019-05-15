<?php namespace Sheba\Pos\Order\RefundNatures;

use Sheba\Pos\Log\Creator as LogCreator;
use Sheba\Pos\Order\Updater;
use Sheba\Pos\Payment\Creator as PaymentCreator;

abstract class ReturnPosItem extends RefundNature
{
    protected $details;
    protected $old_services;
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

    private function refundPayment()
    {
        if (isset($this->data['is_refunded']) && $this->data['is_refunded']) {
            $payment_data['pos_order_id'] = $this->order->id;
            $payment_data['amount'] = $this->data['paid_amount'];

            if ($this->data['paid_amount'] > 0) {
                $payment_data['method'] = $this->data['payment_method'];
                $this->paymentCreator->credit($payment_data);
            } else {
                $payment_data['amount'] = abs($payment_data['amount']);
                $this->paymentCreator->debit($payment_data);
            }
        }
    }

    /**
     * GENERATE LOG DETAILS DATA
     */
    protected function generateDetails()
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
}