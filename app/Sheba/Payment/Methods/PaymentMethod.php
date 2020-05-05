<?php namespace Sheba\Payment\Methods;

use App\Models\Payable;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Carbon\Carbon;
use Sheba\ModificationFields;

abstract class PaymentMethod
{
    use ModificationFields;

    protected $paymentRepository;
    const VALIDITY_IN_MINUTES = 3;

    public function __construct()
    {
        $this->paymentRepository = new PaymentRepository();
    }

    abstract public function init(Payable $payable): Payment;

    abstract public function validate(Payment $payment);

    /**
     * @return Carbon
     */
    protected function getValidTill()
    {
        return Carbon::now()->addMinutes($this->getValidityInMinutes());
    }

    public function getValidityInMinutes()
    {
        return self::VALIDITY_IN_MINUTES;
    }
}