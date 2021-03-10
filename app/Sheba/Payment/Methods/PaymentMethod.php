<?php namespace Sheba\Payment\Methods;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Repositories\PaymentStatusChangeLogRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;
use Sheba\Payment\StatusChanger;
use Sheba\Payment\Statuses;
use Sheba\RequestIdentification;

abstract class PaymentMethod
{
    const VALIDITY_IN_MINUTES = 3;

    use ModificationFields;

    /** @var PaymentStatusChangeLogRepository */
    protected $paymentLogRepo;

    /** @var StatusChanger */
    protected $statusChanger;

    public function __construct()
    {
        $this->paymentLogRepo = new PaymentStatusChangeLogRepository();

        /** @var StatusChanger $s */
        $s                   = app(StatusChanger::class);
        $this->statusChanger = $s;
    }

    /**
     * @param Payable $payable
     * @return Payment
     */
    abstract public function init(Payable $payable): Payment;

    /**
     * @param Payment $payment
     * @return Payment
     */
    abstract public function validate(Payment $payment): Payment;

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

    abstract public function getMethodName();

    /**
     * @param Payable $payable
     * @param string  $gateway_account_name
     * @return Payment
     * @throws \Exception
     */
    protected function createPayment(Payable $payable, $gateway_account_name = 'default'): Payment
    {
        $payment = new Payment();
        $user    = $payable->user;

        $invoice = "SHEBA_" . strtoupper($this->getMethodName()) . "_" .
                   strtoupper($payable->readable_type) . '_' . $payable->type_id . '_' .randomString(10, 1, 1);

        DB::transaction(function () use (&$payment, $payable, $invoice, $user, $gateway_account_name) {
            $payment->payable_id             = $payable->id;
            $payment->transaction_id         = $invoice;
            $payment->gateway_transaction_id = $invoice;
            $payment->gateway_account_name   = $gateway_account_name;
            $payment->status                 = Statuses::INITIATED;
            $payment->valid_till             = $this->getValidTill();
            $payment->request_payload        = json_encode(request()->all());
            $this->setModifier($user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();

            $payment_details             = new PaymentDetail();
            $payment_details->payment_id = $payment->id;
            $payment_details->method     = $this->getMethodName();
            $payment_details->amount     = $payable->amount;
            $payment_details->save();
        });

        return $payment;
    }
}
