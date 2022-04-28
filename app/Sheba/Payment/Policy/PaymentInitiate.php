<?php namespace Sheba\Payment\Policy;


use App\Models\PartnerOrder;
use App\Models\Payable;
use Carbon\Carbon;
use Sheba\Dal\Payable\Types;
use Sheba\Dal\Payment\PaymentRepositoryInterface;
use Sheba\PartnerOrder\ConcurrentUpdateRestriction\ConcurrentUpdateRestriction as CURestriction;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Methods\PaymentMethod;

class PaymentInitiate
{

    private $payableType;
    private $payableTypeId;
    /** @var Payable */
    private $payable;
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
     * @param Payable $payable
     * @return PaymentInitiate
     */
    public function setPayable($payable)
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @return bool true if possible
     * @throws InitiateFailedException otherwise
     */
    public function canPossible()
    {
        if ($this->hasOngoingPayment()) throw new InitiateFailedException($this->getErrorMessageForOngoingPayment(), 400);
        if ($this->hasConcurrentUpdateRestriction()) {
            $concurrent_update_object = CURestriction::getCUObject(PartnerOrder::find($this->payable->type_id));
            if (Carbon::now() > Carbon::parse($concurrent_update_object['created_at'])->addSeconds(constants('MAX_CONCURRENT_TIME'))) {
                CURestriction::remove(PartnerOrder::find($this->payable->type_id));
                return true;
            };
            throw new InitiateFailedException($this->getErrorMessageForConcurrentRestriction(), 400);
        }
        return true;
    }


    /**
     * @return bool
     */
    private function hasOngoingPayment()
    {
        return $this->paymentRepository->getUnresolvedPaymentsFor($this->payable->type, $this->payable->type_id, $this->payable->user_id, $this->payable->user_type)->count() > 0;
    }

    /**
     * @return bool
     */
    private function hasConcurrentUpdateRestriction()
    {
        return $this->payable->type == Types::PARTNER_ORDER && CURestriction::check(PartnerOrder::find($this->payable->type_id));
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
