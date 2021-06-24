<?php namespace App\Jobs\Partner\PaymentLink;

use App\Jobs\Job;
use App\Models\Payment;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Sms\Sms;

class SendPaymentLinkSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $paymentLink;
    private $payment;

    public function __construct(Payment $payment, PaymentLinkTransformer $paymentLink)
    {
        $this->payment = $payment;
        $this->paymentLink = $paymentLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PaymentLinkRepositoryInterface $repo)
    {
        if ($this->attempts() > 2) return;

        $money_receipt = null;
        if ($this->payment->invoice_link) {
            $url = $repo->createShortUrl($this->payment->invoice_link);
            $money_receipt = $url->getShortUrl();
        }
        $formatted_collected_amount = number_format($this->payment->payable->amount, 2);
        $name = $this->payment->payable->getName();
        $payment_receiver = $this->paymentLink->getPaymentReceiver();
        $log = "$formatted_collected_amount TK has been collected from " . $name . " by link-" . $this->paymentLink->getLinkID() . ". Please see the sManager app for detail information.";
        (new Sms())
            ->setFeatureType(FeatureType::PAYMENT_LINK)
            ->setBusinessType(BusinessType::SMANAGER)
            ->shoot($payment_receiver->getMobile(), $log);
        $target = $this->paymentLink->getTarget();
        $variable = "paid $formatted_collected_amount TK";
        if ($target) $variable = "placed an order, ID : {$target->id}.Amount $formatted_collected_amount TK has been paid";
        $log = "You have successfully $variable To {$payment_receiver->name} through {$this->payment->paymentDetails->last()->readable_method}.";
        $log .= $money_receipt ? " Money receipt: $money_receipt" : '';
        (new Sms())
            ->setFeatureType(FeatureType::PAYMENT_LINK)
            ->setBusinessType(BusinessType::SMANAGER)
            ->shoot($this->payment->payable->getMobile(), $log);
    }
}