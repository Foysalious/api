<?php namespace App\Sheba\Pos\Order\Listeners;

use App\Jobs\Partner\Pos\GenerateOrderInvoice;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sheba\Dal\Extras\Events\BaseEvent;
use Sheba\Dal\POSOrder\Events\PosOrderSaved as PosOrderSavedEvent;

class PosOrderSaved
{
    use DispatchesJobs;
    /**
     * @param PosOrderSavedEvent $event
     */
    public function handle(BaseEvent $event)
    {
       print_r('111111');
        $this->dispatchNow((new GenerateOrderInvoice($event->model)));
    }


}