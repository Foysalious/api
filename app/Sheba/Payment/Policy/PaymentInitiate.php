<?php namespace App\Sheba\Payment\Policy;


use App\Models\PartnerOrder;
use Sheba\Dal\Payable\Types;
use Sheba\Dal\Payment\PaymentRepositoryInterface;
use Sheba\PartnerOrder\ConcurrentUpdateRestriction\ConcurrentUpdateRestriction as CURestriction;
use Sheba\Payment\Adapters\Error\InitiateFailedException;
use Sheba\Payment\Methods\PaymentMethod;

class PaymentInitiate
{

    private $payableType;
    private $payableTypeId;
    private $paymentRepository;
    /** @var PaymentMethod */
    private $paymentMethod;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @param string $payableType
     * @return PaymentInitiate
     */
    public function setPayableType($payableType)
    {
        $this->payableType = $payableType;
        return $this;
    }

    /**
     * @param mixed $payableTypeId
     * @return PaymentInitiate
     */
    public function setPayableTypeId($payableTypeId)
    {
        $this->payableTypeId = $payableTypeId;
        return $this;
    }

    /**
     * @param PaymentMethod $paymentMethod
     * @return PaymentInitiate
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @return int
     * @throws InitiateFailedException
     */
    public function canPossible()
    {
        if ($this->paymentRepository->getOngoingPaymentsFor($this->payableType, $this->payableTypeId)->count() > 0) throw new InitiateFailedException($this->constructMessage(), 400);
        if ($this->payableType == Types::PARTNER_ORDER && CURestriction::check(PartnerOrder::find($this->payableTypeId))) throw new InitiateFailedException($this->constructMessage(), 400);
        return 1;
    }

    private function constructMessage()
    {
        return 'Please wait until your previous initiation of online payment expires within ' . $this->paymentMethod->getValidityInMinutes() . ' minutes.';
    }
}