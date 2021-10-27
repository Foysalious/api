<?php namespace Sheba\Pos\Order;


use App\Models\Partner;
use App\Models\PosOrder;
use App\Transformers\CustomSerializer;
use App\Transformers\Pos\Order\WebstoreOrderListTransformer;
use App\Transformers\PosOrderTransformer;
use Carbon\Carbon;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\Helpers\TimeFrame;
use Sheba\PaymentLink\Target;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;

class PosOrderList
{
    /** @var Partner */
    protected $partner;
    protected $status;
    protected $offset;
    protected $limit;
    /** @var SalesChannels */
    protected $sales_channel;
    protected $q;
    protected $type;

    /** @var PaymentLinkRepositoryInterface */
    private $paymentLinkRepo;
    protected $orderStatus;

    public function __construct()
    {
        $this->sales_channel = SalesChannels::POS;
        $this->paymentLinkRepo = app(PaymentLinkRepositoryInterface::class);
    }

    /**
     * @param Partner $partner
     * @return PosOrderList
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param $status
     * @return PosOrderList
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $sales_channel
     * @return PosOrderList
     */
    public function setSalesChannel($sales_channel)
    {
        $this->sales_channel = $sales_channel;
        return $this;
    }

    /**
     * @param $q
     * @return PosOrderList
     */
    public function setQuery($q)
    {
        $this->q = $q;
        return $this;
    }

    public function setPaymentStatus($payment_status)
    {
        $this->payment_status = $payment_status;
        return $this;
    }

    /**
     * @param $type
     * @return PosOrderList
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param $orderStatus
     * @return PosOrderList
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;
        return $this;
    }

    /**
     * @param $offset
     * @return PosOrderList
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param $limit
     * @return PosOrderList
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function get()
    {
        /** @var PosOrder $orders */
        $orders = $this->getFilteredOrders();

        if ($this->sales_channel == SalesChannels::WEBSTORE) {
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Collection($orders, new WebstoreOrderListTransformer());
            return $fractal->createData($resource)->toArray()['data'];
        }
        $final_orders = collect();
        $payment_link_targets = [];

        foreach ($orders as $index => $order) {
            $order->isRefundable();
            $order_data = $order->calculate();
            $manager    = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource        = new Item($order_data, new PosOrderTransformer());
            $order_formatted = $manager->createData($resource)->toArray()['data'];
            if (array_key_exists('payment_link_target', $order_formatted)) {
                $payment_link_targets[] = $order_formatted['payment_link_target'];
            }
            $final_orders->push($order_formatted);
        }

        if (!empty($payment_link_targets)) $this->mapPaymentLinkData($final_orders, $payment_link_targets);
        if (!empty($this->status))
            $final_orders = $final_orders->where('status', $this->status)->slice($this->offset)->take($this->limit);

        $final_orders = $final_orders->groupBy('date')->toArray();

        $orders_formatted = [];
        $pos_orders_repo  = new PosOrderRepository();
        $pos_sales        = [];
        foreach (array_keys($final_orders) as $date) {
            $timeFrame = new TimeFrame();
            $timeFrame->forADay(Carbon::parse($date))->getArray();
            $pos_orders = $pos_orders_repo->getCreatedOrdersBetween($timeFrame, $this->partner);
            $pos_orders->map(function ($pos_order) {
                /** @var PosOrder $pos_order */
                $pos_order->sale = $pos_order->getNetBill();
                $pos_order->paid = $pos_order->getPaid();
                $pos_order->due  = $pos_order->getDue();
            });
            $pos_sales[$date] = [
                'total_sale' => $pos_orders->sum('sale'),
                'total_paid' => $pos_orders->sum('paid'),
                'total_due'  => $pos_orders->sum('due'),
            ];
        }
        foreach ($final_orders as $key => $value) {
            if (count($value) > 0) {
                $order_list = [
                    'date'       => $key,
                    'total_sale' => $pos_sales[$key]['total_sale'],
                    'total_paid' => $pos_sales[$key]['total_paid'],
                    'total_due'  => $pos_sales[$key]['total_due'],
                    'orders'     => $value
                ];
                array_push($orders_formatted, $order_list);
            }
        }
        return $orders_formatted;
    }

    private function getFilteredOrders()
    {
        $orders_query = PosOrder::salesChannel($this->sales_channel)->with('items.service.discounts', 'customer.profile', 'payments', 'logs', 'partner')->byPartner($this->partner->id);
        if ($this->type) $orders_query = $this->filteredByType($orders_query, $this->type);
        if ($this->orderStatus) $orders_query = $this->filteredByOrderStatus($orders_query, $this->orderStatus);
        if ($this->q) $orders_query = $this->filteredBySearchQuery($orders_query, $this->q);
        return empty($this->status) ? $orders_query->orderBy('created_at', 'desc')->skip($this->offset)->take($this->limit)->get() : $orders_query->orderBy('created_at', 'desc')->get();
    }

    private function filteredBySearchQuery($orders_query, $search_query)
    {
        $partner_id = $this->partner->id;
        $orders_query = $orders_query->where(function ($query) use($search_query, $partner_id){
            $query->whereHas('customer.profile', function ($query) use ($search_query) {
                $query->orWhere('profiles.name', 'LIKE', '%' . $search_query . '%');
                $query->orWhere('profiles.email', 'LIKE', '%' . $search_query . '%');
                $query->orWhere('profiles.mobile', 'LIKE', '%' . $search_query . '%');
            })->orWhereHas('customer.partnerPosCustomer', function($query) use ($search_query, $partner_id) {
                $query->where('partner_id', $partner_id);
                $query->where('partner_pos_customers.nick_name', 'LIKE', '%' . $search_query . '%');
            });
        });
        $orders_query = $orders_query->orWhere(function ($q) use ($search_query) {
            $q->where('pos_orders.partner_wise_order_id', 'LIKE', '%' . $search_query . '%')
                ->where('pos_orders.partner_id', $this->partner->id)
                ->where('pos_orders.sales_channel', $this->sales_channel);
        });
        return $orders_query;
    }

    private function filteredByType($orders_query, $type)
    {
        if ($type == 'new') $orders_query = $orders_query->where('status', OrderStatuses::PENDING);
        if ($type == 'running') $orders_query = $orders_query->whereIn('status', [OrderStatuses::PROCESSING, OrderStatuses::SHIPPED]);
        if ($type == 'completed') $orders_query = $orders_query->whereIn('status', [OrderStatuses::COMPLETED, OrderStatuses::CANCELLED, OrderStatuses::DECLINED]);
        return $orders_query;
    }

    private function mapPaymentLinkData(&$final_orders, $payment_link_targets)
    {
        $payment_links = $this->paymentLinkRepo->getActivePaymentLinksByPosOrders($payment_link_targets);

        $final_orders = $final_orders->map(function ($order) use ($payment_links) {
            if (array_key_exists('payment_link_target', $order)) {
                $key = $order['payment_link_target']->toString();
                if (array_key_exists($key, $payment_links) && $payment_links[$key][0]) {
                    (new PosOrderTransformer())->addPaymentLinkDataToOrder($order, $payment_links[$key][0]);
                }
                unset($order['payment_link_target']);
            }
            return $order;
        });
    }

    private function filteredByOrderStatus($orders_query, $orderStatus)
    {
        $orders_query = $orders_query->where('status', $orderStatus);
        return $orders_query;
    }

    private function filteredByStatus($orders_query, $status)
    {
        return $orders_query->where('payment_status', $status);
    }


}