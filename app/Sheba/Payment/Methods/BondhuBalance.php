<?php namespace Sheba\Payment\Methods;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use DB;

use Carbon\Carbon;

use Sheba\RequestIdentification;
use Sheba\ModificationFields;

class BondhuBalance extends PaymentMethod
{
    use ModificationFields;

    /**
     * @param Payable $payable
     * @return Payment
     */
    public function init(Payable $payable): Payment
    {
        $invoice = 'BONDHU_BALANCE_' . strtoupper($payable->readable_type) . '_' . $payable->type_id . '_' . randomString(10, true, true);
        $payment = new Payment();
        DB::transaction(function () use ($payment, $payable, $invoice) {
            $payment->payable_id = $payable->id;
            $payment->transaction_id = $invoice;
            $payment->status = 'initiated';
            $payment->valid_till =$this->getValidTill();
            $this->setModifier($payable->user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();

            $this->savePaymentDetail($payment, $payable->amount, 'bondhu_balance');
        });

        return $payment;
    }

    /**
     * @param Payment $payment
     * @param $amount
     * @param $method
     */
    private function savePaymentDetail(Payment $payment, $amount, $method)
    {
        $payment_details = new PaymentDetail();
        $payment_details->payment_id = $payment->id;
        $payment_details->method = $method;
        $payment_details->amount = $amount;
        $this->setModifier($payment->payable->user);
        $payment_details->created_at = Carbon::now();
        $this->withCreateModificationField($payment_details);
        $payment_details->save();
    }

    /**
     * @param Payment $payment
     * @return Payment
     */
    public function validate(Payment $payment): Payment
    {
        return $payment;
    }

    public function getMethodName()
    {
        return "bondhu_balance";
    }
}
