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
    private $checkNumber;
    private $bankName;
    private $portalName;
    private $attachment;
    private $transactionId;
    private $transactionDetail;

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

    public function setCheckNumber($check_number)
    {
        $this->checkNumber = $check_number;
        return $this;
    }

    public function setBankName($bank_name)
    {
        $this->bankName = $bank_name;
        return $this;
    }

    public function setPortalName($portal_name)
    {
        $this->portalName = $portal_name;
        return $this;
    }

    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
        return $this;
    }

    public function setAttachmentId($transaction_id)
    {
        $this->transactionId = $transaction_id;
        return $this;
    }

    private function formatTransactionDetail()
    {
        $this->transactionDetail = json_encode([
            'check_number' => $this->checkNumber,
            'bank_name' => $this->bankName,
            'attachment' => $this->attachment,
            'transaction_id' => $this->transactionId
        ]);
    }

    public function create()
    {
        $this->formatTransactionDetail();
        return $this->paymentRepository->create([
            'amount' => $this->amount,
            'transaction_type' => $this->paymentType,
            'method' => $this->method,
            'procurement_id' => $this->procurement->id,
            'log' => $this->log,
            'portal_name' => $this->portalName,
            'transaction_detail' => $this->transactionDetail
        ]);
    }
}

