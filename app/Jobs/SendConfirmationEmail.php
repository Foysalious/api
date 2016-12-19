<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendConfirmationEmail extends Job implements ShouldQueue {
    use InteractsWithQueue, SerializesModels;

    private $customer;
    private $order;

    /**
     * Create a new job instance.
     *
     * @param Customer $customer
     * @param Order $order
     */
    public function __construct(Customer $customer, Order $order)
    {
        $this->customer = $customer;
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @param Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $mailer->send('orders.order-verfication', ['customer' => $this->customer, 'order' => $this->order], function ($m)
        {
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
            $m->to($this->customer->email)->subject('Order Verification');
        });
    }
}
