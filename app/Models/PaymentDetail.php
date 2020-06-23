<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\PaymentDetail\AvailableMethods;

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
            'transaction_id' => $this->payment->transaction_id,
            'gateway' => $this->method,
            'details' => json_decode($this->payment->transaction_details)
        );
    }

    public function getReadableMethodAttribute()
    {
        return AvailableMethods::getReadableName($this->method);
    }
}
