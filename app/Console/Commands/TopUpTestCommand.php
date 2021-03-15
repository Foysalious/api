<?php namespace App\Console\Commands;

use App\Models\TopUpOrder;
use Illuminate\Console\Command;
use Sheba\TopUp\TopUp;
use Sheba\TopUp\Vendor\VendorFactory;

class TopUpTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'top-up-test';

    /**
     * Execute the console command.
     * @param TopUp $top_up
     * @param VendorFactory $vendor_factory
     */
    public function handle(TopUp $top_up, VendorFactory $vendor_factory)
    {
        try {
            $top_up_order = TopUpOrder::find(238911);
            $vendor = $vendor_factory->getById($top_up_order->vendor_id);

            $top_up->setAgent($top_up_order->agent)
                ->setVendor($vendor)
                ->recharge($top_up_order);
        } catch (\Exception $e) {
            dd(get_class($e), $e->getMessage(), simplifyExceptionTrace($e));
        }
    }
}
