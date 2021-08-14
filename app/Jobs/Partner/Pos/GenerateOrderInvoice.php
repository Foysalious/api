<?php namespace App\Jobs\Partner\Pos;

use App\Jobs\Job;
use App\Sheba\Pos\Order\Invoice\InvoiceService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class GenerateOrderInvoice extends Job implements ShouldQueue
{
    protected $model;
    use InteractsWithQueue, SerializesModels;

    public function __construct($model)
    {
        $this->model = $model;
        $this->queue = 'invoice_generation';
    }

    public function handle()
    {
        try {
            dd(1);
            /** @var InvoiceService $invoiceService */
            $invoiceService= app(InvoiceService::class);
            $invoiceService->setPosOrder($this->model)->generateInvoice()->saveInvoiceLink();
        } catch (Exception $e) {
            logError($e->getMessage());
        }
    }

}