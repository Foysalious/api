<?php namespace Sheba\Business\ProcurementPayment;

use App\Models\Procurement;
use Sheba\Dal\ProcurementPayment\ProcurementPaymentRepositoryInterface;

class Creator
{
    private $paymentRepository;
    private $procurement;
    private $amount;
    private $paymentType;
    private $method;
    private $log;

    public function __construct(ProcurementPaymentRepositoryInterface $payment_repository)
    {
        $this->paymentRepository = $payment_repository;
    }

    public function setProcurement(Procurement $procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setPaymentType($type)
    {
        $this->paymentType = ucfirst($type);
        return $this;
    }

    public function setPaymentMethod($method)
    {
        $this->method = strtolower($method);
        return $this;
    }

    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    public function create()
    {
        return $this->paymentRepository->create([
            'amount' => $this->amount,
            'transaction_type' => $this->paymentType,
            'method' => $this->method,
            'procurement_id' => $this->procurement->id,
            'log' => $this->log
        ]);
    }
}
