<?php

namespace App\Jobs;

use App\Models\Order;
use App\Sheba\Pap\Pap;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculatePapAffiliateId extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle()
    {
        $this->order->pap_affiliate_id = (new Pap())->getAffiliateId($this->order->pap_visitor_id);
        $this->order->update();
    }
}
