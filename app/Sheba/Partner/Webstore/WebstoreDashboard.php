<?php namespace Sheba\Partner\Webstore;


use App\Models\Partner;
use App\Models\PosOrder;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\Helpers\TimeFrame;
use Sheba\Pos\Repositories\PosOrderRepository;

class WebstoreDashboard
{
    /**
     * @var Partner
     */
    protected $partner;
    protected $timeFrame;
    /**
     * @var PosOrderRepository
     */
    protected $posOrderRepository;

    public function __construct(PosOrderRepository $posOrderRepository)
    {
        $this->posOrderRepository = $posOrderRepository;
    }

    /**
     * @param Partner $partner
     * @return WebstoreDashboard
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param TimeFrame $timeFrame
     * @return WebstoreDashboard
     */
    public function setTimeFrame(TimeFrame $timeFrame)
    {
        $this->timeFrame = $timeFrame;
        return $this;
    }

    private function getProductStats()
    {
        $product_stats['total_products'] = $this->partner->posServices()->count();
        $product_stats['total_published_products'] = $this->partner->posServices()->publishedForShop()->count();
        return $product_stats;
    }

    private function getOrderStats()
    {
        $order_stats['pending_order'] = $this->partner->posOrders()->webstoreOrders()->pending()->count();
        $order_stats['processing_order'] = $this->partner->posOrders()->webstoreOrders()->processing()->count();
        $order_stats['shipped_order'] = $this->partner->posOrders()->webstoreOrders()->shipped()->count();
        $order_stats['completed_order'] = $this->partner->posOrders()->webstoreOrders()->completed()->count();
        $order_stats['declined_order'] = $this->partner->posOrders()->webstoreOrders()->declined()->count();
        $order_stats['cancelled_order'] = $this->partner->posOrders()->webstoreOrders()->cancelled()->count();
        return $order_stats;
    }

    private function getSalesStats()
    {
        $webstore_orders = $this->posOrderRepository->getCreatedWebstoreOrdersBetweenDateByPartner($this->timeFrame, $this->partner);
        $webstore_orders->map(function ($webstore_order) {
            /** @var PosOrder $webstore_order */
            $webstore_order->sale = $webstore_order->getNetBill();

        });
        $webstore_sales_count = $webstore_orders->count();
        $webstore_sales = $webstore_orders->where('status', OrderStatuses::COMPLETED)->sum('sale');

        $sales_stats['total_order'] = $webstore_sales_count;
        $sales_stats['total_sales'] = $webstore_sales;
        return $sales_stats;
    }

    public function get()
    {
        $stats['product_stats'] = $this->getProductStats();
        $stats['order_stats'] = $this->getOrderStats();
        $stats['sales_stats'] = $this->getSalesStats();
        $stats['is_inventory_empty'] = !$this->partner->posServices()->count() ? 1 : 0;
        $stats['is_registered_for_delivery'] = $this->partner->deliveryInformation ? 1 :0;
        $stats['delivery_charge'] = $this->partner->delivery_charge;
        return $stats;
    }
}