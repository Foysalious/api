<?php

namespace App\Models;


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
        return $this->status == 'validation_failed';
    }

    public function isPassed()
    {
        return $this->status == 'validated' || $this->status == 'failed';
    }


    public function scopeValid($query)
    {
        return $query->where('status', '<>', 'validation_failed');
    }

}