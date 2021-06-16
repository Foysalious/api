<?php namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Customer;
use App\Models\Order;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\Sms\Sms;

class SendOrderConfirmationSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $customer;
    private $order;
    private $sms;
    /** @var Sms */

    /**
     * Create a new job instance.
     *
     * @param Customer $customer
     * @param Order $order
     */
    public function __construct($customer, $order)
    {
        $this->customer = $customer;
        $this->order = $order;
        $this->sms = new Sms();//app(Sms::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->order->calculate();
        $message = "Thanks for placing order at Sheba.xyz. Order ID: " . $this->order->code() . ". Plz check email for details or log into www.sheba.xyz. Helpline: 16516";

        $this->sms
            ->setFeatureType(FeatureType::MARKET_PLACE_ORDER)
            ->setBusinessType(BusinessType::MARKETPLACE)
            ->shoot($this->order->delivery_mobile, $message);
    }
}
