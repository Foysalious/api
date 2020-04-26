<?php namespace Sheba\Payment\Policy;


use App\Models\PartnerOrder;
use Sheba\Dal\Payable\Types;
use Sheba\Dal\Payment\PaymentRepositoryInterface;
use Sheba\PartnerOrder\ConcurrentUpdateRestriction\ConcurrentUpdateRestriction as CURestriction;
use Sheba\Payment\Exceptions\InitiateFailedException;
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
     * @return bool true if possible
     * @throws InitiateFailedException otherwise
     */
    public function canPossible()
    {
        if ($this->hasOngoingPayment()) throw new InitiateFailedException($this->getErrorMessageForOngoingPayment(), 400);
        if ($this->hasConcurrentUpdateRestriction()) throw new InitiateFailedException($this->getErrorMessageForConcurrentRestriction(), 400);
        return true;
    }

    /**
     * @return bool
     */
    private function hasOngoingPayment()
    {
        return $this->paymentRepository->getOngoingPaymentsFor($this->payableType, $this->payableTypeId)->count() > 0;
    }

    /**
     * @return bool
     */
    private function hasConcurrentUpdateRestriction()
    {
        return $this->payableType == Types::PARTNER_ORDER && CURestriction::check(PartnerOrder::find($this->payableTypeId));
    }

    private function getErrorMessageForOngoingPayment()
    {
        return 'Please wait until your previous initiation of online payment expires within ' . $this->paymentMethod->getValidityInMinutes() . ' minutes.';
    }

    private function getErrorMessageForConcurrentRestriction()
    {
        return 'Your order is currently updating. Please try after some time.';
    }

}