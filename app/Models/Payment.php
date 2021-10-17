<?php namespace App\Models;

use Carbon\Carbon;
use Sheba\Dal\BaseModel;
use Sheba\Payment\Statuses;
use Sheba\Transactions\DTO\ShebaTransaction;

class Payment extends BaseModel
{
    protected $guarded = ['id'];

    /**
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

    public function externalPayments()
    {
        return $this->hasOne(\Sheba\Dal\ExternalPayment\Model::class);
    }

    /**
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

    public function scopeInitiationFailed($query)
    {
        return $query->where('status', Statuses::INITIATION_FAILED);
    }

    public function scopeStillValidityLeft($query)
    {
        return $query->where('valid_till', '>', Carbon::now());
    }

    public function scopeNotResolved($query)
    {
        return $query->whereIn('status', array(Statuses::INITIATED, Statuses::VALIDATED));
    }

    /**
     *
     * Other functions
     */
    public function isComplete()
    {
        $this->reload();
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
        return $this->status != Statuses::VALIDATION_FAILED || $this->status != Statuses::INITIATION_FAILED || $this->status != Statuses::FAILED;
    }

    public function canComplete()
    {
        $this->reload();
        return $this->status == Statuses::VALIDATED || $this->status == Statuses::FAILED;
    }

    public function isReturnedFrom(): bool
    {
        return true;
    }

    public function getFormattedPayment(): array
    {
        return [
            'transaction_id' => $this->transaction_id,
            'id'             => (int)$this->payable->type_id,
            'type'           => $this->payable->readable_type,
            'link'           => $this->redirect_url,
            'success_url'    => $this->payable->success_url,
            'fail_url'       => $this->payable->fail_url
        ];
    }

    /**
     * @return ShebaTransaction
     */
    public function getShebaTransaction()
    {
        $detail      = $this->paymentDetails->first();
        $transaction = new ShebaTransaction();
        $transaction->setTransactionId($this->transaction_id)
                    ->setGateway($detail ? $detail->method : null)
                    ->setDetails(json_decode($this->transaction_details));

        return $transaction;
    }

    public function getValidityInSeconds()
    {
        return Carbon::now()->diffInSeconds($this->valid_till);
    }

    public function getTransactionDetails()
    {
        return json_decode($this->transaction_details);
    }

    public function getErrorMessage()
    {
        $details = $this->getTransactionDetails();
        return $details && property_exists($details, 'errorMessage') ? $details->errorMessage : null;
    }
}
