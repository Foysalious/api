<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Library\Sms;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmationSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $customer;
    private $order;

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
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->order->calculate();
        $message = "Thanks for placing order at Sheba.xyz. Order ID: " . $this->order->code() . " Min Est. cost " . $this->order->totalPrice . " TK. Plz check email for details or log into www.sheba.xyz. Helpline:16516";
        Sms::send_single_message($this->order->delivery_mobile, $message);
    }
}
