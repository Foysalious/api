<?php namespace Sheba\Resource\WithdrawalRequest;


use App\Models\Resource;
use App\Models\WithdrawalRequest;
use App\Sheba\UserRequestInformation;
use Sheba\Dal\WithdrawalRequest\RequesterTypes;

class Creator
{
    /**
     * @var Resource
     */
    private $resource;
    private $requesterType;
    private $amount;
    private $paymentMethod;
    private $bkashNumber;
    private $userRequestInformation;

    /**
     * @param Resource $resource
     * @return $this
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param $requesterType
     * @return $this
     */
    public function setRequesterType($requesterType)
    {
        $this->requesterType = $requesterType;
        return $this;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @param $bkashNumber
     * @return $this
     */
    public function setBkashNumber($bkashNumber)
    {
        $this->bkashNumber = $bkashNumber;
        return $this;
    }

    public function setRequestUserInformation($userRequestInformation)
    {
        $this->userRequestInformation = $userRequestInformation;
        return $this;
    }

    public function create()
    {
        return WithdrawalRequest::create(array_merge( $this->userRequestInformation, [
            'requester_id' => $this->resource->id,
            'requester_type' => $this->requesterType,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'payment_info' => json_encode(['bkash_number' => $this->bkashNumber]),
            'created_by_type' => class_basename($this->resource),
            'created_by' => $this->resource->id,
            'created_by_name' => 'Resource - ' . $this->resource->profile->name,
        ]));
    }

}