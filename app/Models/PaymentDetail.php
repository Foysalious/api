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
            'name' => $this->getReadableMethodAttribute(),
            'details' => array(
                'transaction_id' => $this->payment->transaction_id,
                'gateway' => $this->method,
                'details' => $this->payment->transaction_details
            )
        );
    }

    public function getReadableMethodAttribute()
    {
        if ($this->method == 'ssl')
            return 'Online';
        elseif ($this->method == 'wallet' || $this->method == 'bonus')
            return 'Wallet';
        elseif ($this->method == 'bkash')
            return 'Bkash';
    }
}