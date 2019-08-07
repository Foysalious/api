<?php namespace App\Jobs\Partner\PaymentLink;

use App\Jobs\Job;
use App\Models\Payment;
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
    private $sms;
    private $paymentLinkRepository;


    public function __construct(Payment $payment, PaymentLinkTransformer $paymentLink)
    {
        $this->payment = $payment;
        $this->paymentLink = $paymentLink;
        $this->sms = new Sms();//app(Sms::class);
        $this->paymentLinkRepository = app(PaymentLinkRepositoryInterface::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (config('sheba.payment_link.sms') && $this->attempts() <= 2) {
            $money_receipt = null;
            if ($this->payment->invoice_link) {
                $url = $this->paymentLinkRepository->createShortUrl($this->payment->invoice_link);
                $money_receipt = $url->getShortUrl();
            }
            $formatted_collected_amount = number_format($this->payment->payable->amount, 2);
            $name = $this->payment->payable->getName();
            $payment_receiver = $this->paymentLink->getPaymentReceiver();
            $log = "$formatted_collected_amount TK has been collected from " . $name . " by link- " . $this->paymentLink->getLinkID() . ". Please see the sManager app for detail information.";
            $this->sms->shoot($payment_receiver->getMobile(), $log);
            $target = $this->paymentLink->getTarget();
            $variable = "paid $formatted_collected_amount TK";
            if ($target) $variable = "placed an order, ID: {$target->id}. $formatted_collected_amount TK has been paid";
            $log = "You have successfully $variable To {$payment_receiver->name} through {$this->payment->paymentDetails()->last->readable_method}." . $money_receipt ? " Money receipt: $money_receipt" : '';
            $this->sms->shoot($this->payment->payable->getMobile(), $log);
        }
    }
}