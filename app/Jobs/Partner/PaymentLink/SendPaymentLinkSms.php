<?php namespace App\Jobs\Partner\PaymentLink;

use App\Jobs\Job;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Sms\Sms;

class SendPaymentLinkSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $paymentLink;
    private $payment;
    private $sms;


    public function __construct(Payment $payment, PaymentLinkTransformer $paymentLink)
    {
        $this->payment = $payment;
        $this->paymentLink = $paymentLink;
        $this->sms = new Sms();//app(Sms::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (config('sheba.payment_link.sms') && $this->attempts() <= 2) {
            $formatted_collected_amount = number_format($this->payment->payable->amount, 2);
            $name = $this->payment->payable->getName();
            $payment_receiver = $this->paymentLink->getPaymentReceiver();
            $log = "$formatted_collected_amount TK has been collected from " . $name . " by link- " . $this->paymentLink->getLinkID() . ". Please see the sManager app for detail information.";
            $this->sms->shoot($payment_receiver->getMobile(), $log);
            $target = $this->paymentLink->getTarget();
            $variable = "paid $formatted_collected_amount TK";
            if ($target) $variable = "placed an order, ID: {$target->id}. $formatted_collected_amount TK has been paid";
            $log = "You have successfully $variable To {$payment_receiver->name} through {$this->payment->paymentDetails()->last->readable_method}. Money receipt: bit.ly/Bhrih";
            $this->sms->shoot($this->payment->payable->getMobile(), $log);
        }
    }
}