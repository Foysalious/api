<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $casts = ['amount' => 'double'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function formatPaymentDetail()
    {
        return array(
            'name' => $this->getPaymentName(),
            'details' => array(
                'transaction_id' => $this->payment->transaction_id,
                'gateway' => $this->name,
                'details' => $this->payment->transaction_details
            )
        );
    }

    private function getPaymentName()
    {
        if ($this->name == 'ssl')
            return 'Online';
        else
            return 'Wallet';
    }
}