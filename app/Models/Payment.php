<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = ['id'];

    public function payable()
    {
        return $this->belongsTo(Payable::class);
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function isComplete()
    {
        return $this->status == 'completed';
    }

    public function isInitiated()
    {
        return $this->status == 'initiated';
    }

    public function isFailed()
    {
        return $this->status == 'validation_failed' || $this->status == 'initiation_failed';
    }

    public function isPassed()
    {
        return $this->status == 'validated' || $this->status == 'failed';
    }

    public function scopeValid($query)
    {
        return $query->where([['status', '<>', 'validation_failed'], ['status', '<>', 'initiation_failed']]);
    }

    public function scopeNotCompleted($query)
    {
        return $query->where('status', '<>', 'completed');
    }

    public function canComplete()
    {
        return $this->status == 'validated' || $this->status == 'failed';
    }

    public function getFormattedPayment()
    {
        return array(
            'transaction_id' => $this->transaction_id,
            'id' => $this->payable->type_id,
            'type' => $this->payable->readable_type,
            'link' => $this->redirect_url,
            'success_url' => $this->payable->success_url
        );
    }
}