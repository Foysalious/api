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
        $this->dispatch((new GenerateOrderInvoice($event->model)));
    }

}