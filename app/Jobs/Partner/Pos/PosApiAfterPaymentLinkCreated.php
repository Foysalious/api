<?php

namespace App\Jobs\Partner\Pos;

use App\Jobs\Job;
use App\Sheba\Pos\Order\Invoice\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PosApiAfterPaymentLinkCreated extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public function __construct()
    {

    }

    public function handle()
    {
        try {
            if ($this->attempts() <= 1) {
                /** @var InvoiceService $invoiceService */
                $invoiceService= app(InvoiceService::class);
                $invoiceService  =  $invoiceService->setPosOrder($this->model);
                $invoiceService->generateInvoice()->saveInvoiceLink();
            }
        } catch (\Throwable $e) {
            logError($e);
        }
    }

}
