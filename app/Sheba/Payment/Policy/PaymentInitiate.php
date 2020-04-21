<?php namespace App\Sheba\Payment\Policy;


use App\Models\PartnerOrder;
use Sheba\Dal\Payable\Types;
use Sheba\Dal\Payment\PaymentRepositoryInterface;
use Sheba\PartnerOrder\ConcurrentUpdateRestriction\ConcurrentUpdateRestriction as CURestriction;
use Sheba\Payment\PayableType;

class PaymentInitiate
{

    private $payableType;
    private $payableTypeId;
    private $paymentRepository;

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

    public function canPossible()
    {
        if ($this->paymentRepository->getOngoingPaymentsFor($this->payableType, $this->payableTypeId)->count() > 0) return 0;
        if ($this->payableType == Types::PARTNER_ORDER && CURestriction::check(PartnerOrder::find($this->payableTypeId))) return 0;
        return 1;
    }
}