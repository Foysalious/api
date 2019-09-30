<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmationEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $profile;
    private $order;

    /**
     * Create a new job instance.
     *
     * @param Customer $profile
     * @param Order $order
     */
    public function __construct($profile, $order)
    {
        $this->profile = $profile;
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
        $mailer->send('emails.order-verification', ['profile' => $this->profile, 'order' => $this->order], function ($m) {
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
            $m->to($this->profile->email)->subject('Order Verification');
        });
    }
}
