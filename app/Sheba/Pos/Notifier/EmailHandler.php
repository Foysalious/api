<?php namespace Sheba\Pos\Notifier;

use App\Models\PosOrder;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailHandler
{
    /**
     * @var PosOrder
     */
    private $order;

    public function setOrder(PosOrder $order)
    {
        $this->order = $order->calculate();
        return $this;
    }

    public function handle()
    {
        try {
            Mail::send('emails.pos-order-bill', ['order' => $this->order], function ($m) {
                $m->to($this->order->customer->profile->email)->subject('Order Bills');
            });
        } catch (Throwable $e){}
    }
}