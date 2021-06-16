<?php namespace App\Console\Commands;

use App\Models\TopUpOrder;
use Illuminate\Console\Command;
use Sheba\TopUp\TopUpLifecycleManager;
use Sheba\TopUp\TopUpRechargeManager;
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
     * @param TopUpRechargeManager $recharge
     * @param TopUpLifecycleManager $lifecycle
     * @param VendorFactory $vendor_factory
     */
    public function handle(TopUpRechargeManager $recharge, TopUpLifecycleManager $lifecycle, VendorFactory $vendor_factory)
    {
        try {
            $top_up_order = TopUpOrder::find(6661608);
            /*$vendor = $vendor_factory->getById($top_up_order->vendor_id);

            $recharge->setAgent($top_up_order->agent)
                ->setVendor($vendor)
                ->setTopUpOrder($top_up_order)
                ->recharge();*/

            dd($lifecycle->setTopUpOrder($top_up_order)->reload()->getResponse());
        } catch (\Exception $e) {
            dde($e);
        }
    }
}
