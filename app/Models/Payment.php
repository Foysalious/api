<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Payment\Statuses;
use Sheba\Transactions\DTO\ShebaTransaction;

class Payment extends Model
{
    protected $guarded = ['id'];

    /**
     *
     * Relationships
     */
    public function payable()
    {
        return $this->belongsTo(Payable::class);
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    /**
     *
     * Scope functions
     */
    public function scopeNotCompleted($query)
    {
        return $query->where('status', '<>', Statuses::COMPLETED);
    }

    public function scopeInitiated($query)
    {
        return $query->where('status', Statuses::INITIATED);
    }

    public function scopeStillValidityLeft($query)
    {
        return $query->where('valid_till', '>', Carbon::now());
    }

    /**
     *
     * Other functions
     */
    public function isComplete()
    {
        return $this->status == Statuses::COMPLETED;
    }

    public function isInitiated()
    {
        return $this->status == Statuses::INITIATED;
    }

    public function isFailed()
    {
        return $this->status == Statuses::VALIDATION_FAILED || $this->status == Statuses::INITIATION_FAILED;
    }

    public function isPassed()
    {
        return $this->status == Statuses::VALIDATED || $this->status == Statuses::FAILED;
    }

    public function scopeValid($query)
    {
        return $query->where([['status', '<>', Statuses::VALIDATION_FAILED], ['status', '<>', Statuses::INITIATION_FAILED]]);
    }

    public function isValid()
    {
        return $this->status != Statuses::VALIDATION_FAILED || $this->status != Statuses::INITIATION_FAILED;
    }


    public function canComplete()
    {
        return $this->status == Statuses::VALIDATED || $this->status == Statuses::FAILED;
    }

    public function getFormattedPayment()
    {
        return [
            'transaction_id' => $this->transaction_id,
            'id' => (int)$this->payable->type_id,
            'type' => $this->payable->readable_type,
            'link' => $this->redirect_url,
            'success_url' => $this->payable->success_url
        ];
    }

    /**
     * @return ShebaTransaction
     */
    public function getShebaTransaction()
    {
        $detail = $this->paymentDetails->first();
        $transaction = new ShebaTransaction();
        $transaction->setTransactionId($this->transaction_id)
            ->setGateway($detail ? $detail->method : null)
            ->setDetails(json_decode($this->transaction_details));

        return $transaction;
    }
}