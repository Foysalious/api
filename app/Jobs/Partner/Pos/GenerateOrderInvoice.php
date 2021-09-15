<?php namespace App\Jobs\Partner\Pos;

use App\Jobs\Job;
use App\Sheba\Pos\Order\Invoice\InvoiceService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateOrderInvoice  extends Job implements ShouldQueue
{
    protected $model;
    use InteractsWithQueue, SerializesModels;

    public function __construct($model)
    {
        $this->model = $model;
        $this->connection = 'invoice_generation';
        $this->queue = 'invoice_generation';
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
        } catch (Exception $e) {
            logError($e->getMessage());
        }
    }

}