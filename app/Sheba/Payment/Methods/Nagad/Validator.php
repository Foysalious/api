<?php


namespace Sheba\Payment\Methods\Nagad;


use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Methods\Nagad\Exception\InvalidOrderId;

class Validator
{
    private $data;
    /** @var Payment $payment */
    private $payment;
    /** @var Payable $payable */
    private $payable;
    private $status = false;

    /**
     * Validator constructor.
     *
     * @param      $data
     * @param bool $resp
     * @throws InvalidOrderId
     */
    public function __construct($data, $resp = false)
    {
        $this->setData($data);
        if (!$resp) {
            $this->setPayment();
        } else {
            $this->setOthers();
        }
    }

    /**
     * @param mixed $data
     * @return Validator
     */
    public function setData($data)
    {
        $this->data = (array)$data;
        return $this;
    }

    public function getPaymentRefId()
    {
        if (isset($this->data['payment_ref_id'])) {
            return $this->data['payment_ref_id'];
        }
        return null;
    }

    public function getOrderID()
    {
        if (!isset($this->data['order_id'])) {
            return null;
        }
        return $this->data['order_id'];
    }

    /**
     * @throws InvalidOrderId
     */
    public function setPayment()
    {
        $order_id = $this->getOrderID();
        if (!empty($order_id)) {
            $this->payment = Payment::where('gateway_transaction_id', $order_id)->first();
            if (empty($this->payment)) throw new InvalidOrderId();
            $this->payable = $this->payment->payable;
        }
    }

    public function getPayment()
    {
        return $this->payment;
    }

    public function getPayable()
    {
        return $this->payable;
    }

    public function toString()
    {
        return json_encode($this->data);
    }

    private function setOthers()
    {
        $this->status = isset($this->data['status']) && $this->data['status'] == 'Success' ? 'paid' : false;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
