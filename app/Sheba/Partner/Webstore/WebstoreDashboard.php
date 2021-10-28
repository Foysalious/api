<?php namespace Sheba\Partner\Webstore;


use App\Models\Partner;
use App\Models\PosOrder;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\PosOrderService\PosOrderServerClient;
use App\Sheba\UserMigration\Modules;
use Carbon\Carbon;
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
    protected $date;
    protected $week;
    protected $year;
    protected $month;
    protected $frequency;
    /**
     * @var PosOrderRepository
     */
    protected $posOrderRepository;
    /**
     * @var PosOrderServerClient
     */
    private $posOrderServerClient;
    /**
     * @var InventoryServerClient
     */
    private $inventoryServerClient;

    public function __construct(PosOrderRepository $posOrderRepository, PosOrderServerClient $posOrderServerClient, InventoryServerClient $inventoryServerClient)
    {
        $this->posOrderRepository = $posOrderRepository;
        $this->posOrderServerClient = $posOrderServerClient;
        $this->inventoryServerClient = $inventoryServerClient;
    }

    /**
     * @param Partner $partner
     * @return WebstoreDashboard
     */
    public function setPartner(Partner $partner): WebstoreDashboard
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $date
     * @return WebstoreDashboard
     */
    public function setDate($date): WebstoreDashboard
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param mixed $week
     * @return WebstoreDashboard
     */
    public function setWeek($week): WebstoreDashboard
    {
        $this->week = $week;
        return $this;
    }

    /**
     * @param mixed $year
     * @return WebstoreDashboard
     */
    public function setYear($year): WebstoreDashboard
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @param mixed $month
     * @return WebstoreDashboard
     */
    public function setMonth($month): WebstoreDashboard
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @param mixed $frequency
     * @return WebstoreDashboard
     */
    public function setFrequency($frequency): WebstoreDashboard
    {
        $this->frequency = $frequency;
        return $this;
    }


    private function getProductStats(): array
    {
        $product_stats['total_products'] = $this->partner->posServices()->count();
        $product_stats['total_published_products'] = $this->partner->posServices()->publishedForShop()->count();
        return $product_stats;
    }

    private function getProductStatsFromInventoryService($stats): array
    {
        $product_stats['total_products'] = $stats['total_products'];
        $product_stats['total_published_products'] = $stats['total_published_products'];
        return $product_stats;
    }

    private function getOrderStats(): array
    {
        $order_stats['pending_order'] = $this->partner->posOrders()->webstoreOrders()->pending()->count();
        $order_stats['processing_order'] = $this->partner->posOrders()->webstoreOrders()->processing()->count();
        $order_stats['shipped_order'] = $this->partner->posOrders()->webstoreOrders()->shipped()->count();
        $order_stats['completed_order'] = $this->partner->posOrders()->webstoreOrders()->completed()->count();
        $order_stats['declined_order'] = $this->partner->posOrders()->webstoreOrders()->declined()->count();
        $order_stats['cancelled_order'] = $this->partner->posOrders()->webstoreOrders()->cancelled()->count();
        return $order_stats;
    }

    private function getOrderStatsFromPosOrderService($status): array
    {
        $order_stats['pending_order'] = $status['Pending'] ?? 0;
        $order_stats['processing_order'] = $status['Processing'] ?? 0;
        $order_stats['shipped_order'] = $status['Shipped'] ?? 0;
        $order_stats['completed_order'] = $status['Completed'] ?? 0;
        $order_stats['declined_order'] = $status['Declined'] ?? 0;
        $order_stats['cancelled_order'] = $status['Cancelled'] ?? 0;
        return $order_stats;
    }

    private function getSalesStats(): array
    {
        $webstore_orders = $this->posOrderRepository->getCreatedWebstoreOrdersBetweenDateByPartner($this->makeTimeFrame(), $this->partner);
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

    private function getSalesStatsFromPosOrderService($statistics): array
    {
        $sales_stats['total_order'] = $statistics['total_order'];
        $sales_stats['total_sales'] = $statistics['total_sales'];
        return $sales_stats;
    }

    public function get()
    {
        list($order_status, $sales_stats) = $this->resolveOrderAndSalesStats();
        $product_stats = $this->resolveProductStats();
        $stats['product_stats'] = $product_stats;
        $stats['order_stats'] = $order_status;
        $stats['sales_stats'] = $sales_stats;
        $stats['is_inventory_empty'] = !$product_stats['total_products'] ? 1 : 0;
        $stats['is_registered_for_delivery'] = $this->partner->deliveryInformation ? 1 :0;
        $stats['delivery_charge'] = $this->partner->delivery_charge;
        return $stats;
    }

    private function resolveProductStats(): array
    {
        if ($this->partner->isMigrated(Modules::POS)) {
            $stats = $this->inventoryServerClient->get('api/v1/partners/'.$this->partner->id.'/statistics');
            return $this->getProductStatsFromInventoryService($stats['statistics']);
        }
        return $this->getProductStats();
    }

    private function resolveOrderAndSalesStats(): array
    {
        if ($this->partner->isMigrated(Modules::POS)) {
            $stats = $this->posOrderServerClient->get('api/v1/partners/'.$this->partner->id.'/statistics?frequency='.$this->frequency. $this->makeParams());
            return [$this->getOrderStatsFromPosOrderService($stats['statistics']['status_count']), $this->getSalesStatsFromPosOrderService($stats['statistics'])];
        }
        return [$this->getOrderStats(), $this->getSalesStats()];
    }

    private function makeTimeFrame(): TimeFrame
    {
        /** @var TimeFrame $time_frame */
        $time_frame = app(TimeFrame::class);
        $date = Carbon::parse($this->date);
        switch ($this->frequency) {
            case "day":
                $time_frame = $time_frame->forADay($date);
                break;
            case "week":
                $time_frame = $time_frame->forSomeWeekFromNow($this->week);
                break;
            case "month":
                $time_frame = $time_frame->forAMonth($this->month, $this->year);
                break;
            case "year":
                $time_frame = $time_frame->forAYear($this->year);
                break;
            case "quarter":
                $time_frame = $time_frame->forAQuarter($date);
                break;
            default:
                echo "Invalid time frame";
        }
        return $time_frame;
    }

    private function makeParams(): string
    {
        $params = '';
        if ($this->year) $params = $params . '&year='.$this->year;
        if ($this->month) $params = $params . '&month='.$this->month;
        if ($this->week) $params = $params . '&week='.$this->week;
        if ($this->date) $params = $params . '&date='.$this->date;
        return $params;
    }
}