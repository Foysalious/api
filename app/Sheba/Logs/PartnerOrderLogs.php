<?php

namespace App\Sheba\Logs;


use App\Models\PartnerOrder;

class PartnerOrderLogs
{
    private $paymentLogs;
    private $partnerOrder;

    public function __construct($partnerOrder)
    {
        $this->partnerOrder = ($partnerOrder instanceof PartnerOrder) ? $partnerOrder : PartnerOrder::find($partnerOrder);
        $this->paymentLogs = collect();
        $this->statusChangeLogs = collect();
    }

    public function all()
    {
        $this->getPayments();
        $this->getStatusChanges();
        return [
            'payments' => $this->paymentLogs,
            'status_change' => $this->statusChangeLogs
        ];
    }

    private function getPayments()
    {
        $payments = $this->partnerOrder->payments->where('transaction_type', 'Debit');
        foreach ($payments as $payment) {
            $this->paymentLogs->push((object)[
                'created_at' => $payment->created_at,
                'created_by_name' => $payment->created_by_name,
            ]);
        }
    }

    private function getStatusChanges()
    {
        $state_changes = $this->partnerOrder->stageChangeLogs;
        foreach ($state_changes as $state_change) {
            $this->statusChangeLogs->push((object)[
                'created_at' => $state_change->created_at,
                'created_by_name' => $state_change->created_by_name,
                'log' => 'Partner Order ' . $this->partnerOrder->code() . ' status has changed from ' . $state_change->from_status . ' to ' . $state_change->to_status,
            ]);
        }
    }

}