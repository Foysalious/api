<?php namespace App\Jobs\Partner\Pos;

use App\Sheba\Pos\Order\Invoice\InvoiceService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\QueueMonitor\MonitoredJob;

class GenerateOrderInvoice extends MonitoredJob implements ShouldQueue
{
    protected $model;
    use InteractsWithQueue, SerializesModels;

    public function __construct($model)
    {
        parent::__construct();
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

    protected function getTitle()
    {
        return "Invoice Generation Job";
    }
}