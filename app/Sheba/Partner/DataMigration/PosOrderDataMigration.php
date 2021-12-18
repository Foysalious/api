<?php namespace App\Sheba\Partner\DataMigration;


use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderLog;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\Partner\DataMigration\Jobs\PartnerDataMigrationToPosOrderJob;
use DB;
use Illuminate\Support\Facades\Redis;
use stdClass;

class PosOrderDataMigration
{
    const CHUNK_SIZE = 10;
    private $currentQueue = 1;
    private $currentChunk;
    /** @var Partner */
    private $partner;
    /** @var array */
    private $partnerPosOrderIds;
    /** @var InventoryServerClient */
    private $inventoryServerClient;
    private $posOrders;
    private $posOrderItems;
    private $posOrderPayments;
    private $posOrderDiscounts;
    private $posOrderLogs;
    private $posCustomers;
    private $skip;
    private $take;
    private $queue_and_connection_name;
    private $shouldQueue;


    public function __construct(InventoryServerClient $inventoryServerClient)
    {
        $this->inventoryServerClient = $inventoryServerClient;
    }

    /**
     * @param mixed $partner
     * @return PosOrderDataMigration
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $queue_and_connection_name
     * @return PosOrderDataMigration
     */
    public function setQueueAndConnectionName($queue_and_connection_name)
    {
        $this->queue_and_connection_name = $queue_and_connection_name;
        return $this;
    }

    /**
     * @param mixed $skip
     * @return PosOrderDataMigration
     */
    public function setSkip($skip)
    {
        $this->skip = $skip;
        return $this;
    }

    /**
     * @param mixed $take
     * @return PosOrderDataMigration
     */
    public function setTake($take)
    {
        $this->take = $take;
        return $this;
    }

    /**
     * @param mixed $shouldQueue
     * @return PosOrderDataMigration
     */
    public function setShouldQueue($shouldQueue)
    {
        $this->shouldQueue = $shouldQueue;
        return $this;
    }

    /**
     * @param mixed $currentChunk
     * @return PosOrderDataMigration
     */
    public function setCurrentChunk($currentChunk)
    {
        $this->currentChunk = $currentChunk;
        return $this;
    }


    public function migrate()
    {
        $this->generateMigrationData();
        $this->migrateOrders($this->posOrders);
    }

    private function generateMigrationData()
    {
        $this->posOrders = $this->generatePosOrdersMigrationData();
        $this->posOrderItems = $this->generatePosOrderItemsData();
        $this->posCustomers = collect($this->generatePosCustomersData());
        $this->posOrderPayments = collect($this->generatePosOrderPaymentsData());
        $this->posOrderDiscounts = collect($this->generatePartnerPosOrderDiscountsMigrationData());
        $this->posOrderLogs = $this->generatePosOrderLogsMigrationData();
    }

    private function migrateOrders($data)
    {
        $chunks = array_chunk($data, self::CHUNK_SIZE);
        foreach ($chunks as $chunk) {
            $posOrderIds = array_column($chunk, 'id');
            $posCustomerIds = array_column($chunk, 'customer_id');
            list($pos_order_items, $pos_order_payments, $pos_order_discounts, $pos_order_logs, $pos_customers) = $this->getPosOrderRelatedData($posOrderIds, $posCustomerIds);
            $this->setRedisKey();
            $this->shouldQueue ? dispatch(new PartnerDataMigrationToPosOrderJob($this->partner, [
                'pos_orders' => $chunk,
                'pos_order_items' => $pos_order_items,
                'pos_order_payments' => $pos_order_payments,
                'pos_order_discounts' => $pos_order_discounts,
                'pos_order_logs' => $pos_order_logs,
                'pos_customers' => $pos_customers
            ], $this->currentChunk, $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue)) : dispatchJobNow(new PartnerDataMigrationToPosOrderJob($this->partner, [
                'pos_orders' => $chunk,
                'pos_order_items' => $pos_order_items,
                'pos_order_payments' => $pos_order_payments,
                'pos_order_discounts' => $pos_order_discounts,
                'pos_order_logs' => $pos_order_logs,
                'pos_customers' => $pos_customers
            ], $this->currentChunk, $this->currentQueue, $this->queue_and_connection_name, $this->shouldQueue));
            $this->increaseCurrentQueueValue();
        }
    }

    private function generatePosOrdersMigrationData()
    {
        $own_delivery = json_encode([
           'name' => 'own_delivery'
        ]);

        $paperfly = json_encode([
           'name' => 'paperfly',
           'image'  => config('pos_delivery.vendor_list_v2.paperfly.icon')
        ]);

        $delivery_vendor_query = DB::raw("(CASE WHEN pos_orders.delivery_vendor_name = 'own_delivery' THEN '$own_delivery'  WHEN pos_orders.delivery_vendor_name = 'sdelivery' THEN '$paperfly' ELSE NULL END) as delivery_vendor");
        $delivery_name = DB::raw("(CASE WHEN partner_pos_customers.nick_name IS NOT NULL THEN partner_pos_customers.nick_name ELSE profiles.name END) as delivery_name");
        $pos_orders = PosOrder::where('pos_orders.partner_id', $this->partner->id)->where(function ($q) {
            $q->where('pos_orders.is_migrated', null)->orWhere('pos_orders.is_migrated', 0);
        })->withTrashed()
            ->leftJoin('pos_order_payments', 'pos_orders.id', '=', 'pos_order_payments.pos_order_id')
            ->leftJoin('pos_customers', 'pos_orders.customer_id', '=', 'pos_customers.id')
            ->leftJoin('profiles', 'profiles.id', '=', 'pos_customers.profile_id')
            ->leftJoin('partner_pos_customers', function($join) {
                $join->on('pos_customers.id', '=', 'partner_pos_customers.customer_id');
                $join->on('partner_pos_customers.partner_id','=','pos_orders.partner_id');
            })
            ->select('pos_orders.id', 'pos_orders.partner_wise_order_id', 'pos_orders.partner_id', 'pos_orders.customer_id', DB::raw('(CASE 
                        WHEN pos_orders.payment_status = "Paid" THEN SUBTIME(pos_order_payments.created_at,"6:00:00") 
                        ELSE NULL 
                        END) AS paid_at'), DB::raw('(CASE 
                        WHEN pos_orders.sales_channel = "pos" THEN "1" 
                        ELSE "2" 
                        END) AS sales_channel_id'), 'pos_orders.emi_month', 'pos_orders.invoice',
                'pos_orders.bank_transaction_charge', 'pos_orders.interest', 'pos_orders.delivery_charge',
                'pos_orders.address AS delivery_address', $delivery_vendor_query, 'pos_orders.delivery_request_id',
                'pos_orders.delivery_thana', 'pos_orders.delivery_district', 'pos_orders.note', 'pos_orders.status','pos_orders.voucher_id',
                DB::raw('SUBTIME(pos_orders.created_at,"6:00:00") as created_at, SUBTIME(pos_orders.updated_at,"6:00:00") as updated_at, 
                SUBTIME(pos_orders.deleted_at,"6:00:00") as deleted_at'),
                'pos_orders.created_by_name', 'pos_orders.updated_by_name',
                $delivery_name, 'profiles.mobile AS delivery_mobile')
            ->skip($this->skip)->take($this->take)->groupBy('id')->get()->toArray();
        $this->partnerPosOrderIds = array_column($pos_orders, 'id');

        return $pos_orders;
    }

    private function generatePosOrderItemsData()
    {
        $pos_order_items = DB::table('pos_order_items')->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'service_id AS sku_id', DB::raw('(CASE 
                        WHEN service_name = "" THEN "Custom Item"
                        WHEN service_name IS NULL THEN "Custom Item"
                        ELSE service_name 
                        END) AS name'), 'quantity',
                'unit_price', 'vat_percentage', 'warranty', 'warranty_unit', 'note', 'created_by_name','updated_by_name',
                DB::raw('SUBTIME(created_at,"6:00:00") as created_at, SUBTIME(updated_at,"6:00:00") as updated_at'))
            ->get();

        if(!is_array($pos_order_items)) {
            $items = $pos_order_items->toArray();
        } else {
            $items = $pos_order_items;
        }
        $service_ids = array_column($items, 'sku_id');
        $sku_ids = $this->getSkuIdsForProducts($service_ids);
        $skus = $sku_ids['skus'];
        $pos_order_items = collect($pos_order_items);
        $pos_order_items->each(function ($item, $key) use ($skus) {
            $item->sku_id = $skus[$item->sku_id] ?? null;
        });
        return $pos_order_items;
    }

    private function generatePosOrderPaymentsData()
    {
        $pos_order_payments = DB::table('pos_order_payments')->whereIn('pos_order_id', $this->partnerPosOrderIds)
            ->select('pos_order_id AS order_id', 'amount', 'transaction_type', 'method', 'method_details', 'emi_month', 'interest',
                'created_by_name', 'updated_by_name', DB::raw('SUBTIME(created_at,"6:00:00") as created_at, SUBTIME(updated_at,"6:00:00") as updated_at'))
            ->get();
        $collection = collect($pos_order_payments);
        $cash_details = json_encode(['payment_method_en' => 'Cash', 'payment_method_bn' => ' নগদ গ্রহন', 'payment_method_icon' => config('s3.url') . 'pos/payment/cash_v2.png']);
        $digital_payment_details = json_encode(['payment_method_en' => 'Digital Payment', 'payment_method_bn' => 'ডিজিটাল পেমেন্ট', 'payment_method_icon' => config('s3.url') . 'pos/payment/digital_collection_v2.png']);
        $other_details = json_encode(['payment_method_en' => 'Others', 'payment_method_bn' => 'অন্যান্য', 'payment_method_icon' => config('s3.url') . 'pos/payment/others_v2.png']);
        $payments = $collection->map(function($payment) use ($cash_details, $digital_payment_details, $other_details) {
            if ($payment->method == 'advance_balance' || $payment->method == 'cod') return [
                "order_id" => $payment->order_id,
                "amount" => $payment->amount,
                "transaction_type" => $payment->transaction_type,
                "method" => $payment->method,
                "method_details" => $cash_details,
                "emi_month" => $payment->emi_month,
                "interest" => $payment->interest,
                "created_by_name" => $payment->created_by_name,
                "updated_by_name" => $payment->updated_by_name,
                "created_at" => $payment->created_at,
                "updated_at" => $payment->updated_at,
            ];
            if ($payment->method == 'later' || $payment->method == 'transfer' || $payment->method == 'others') return [
                "order_id" => $payment->order_id,
                "amount" => $payment->amount,
                "transaction_type" => $payment->transaction_type,
                "method" => $payment->method,
                "method_details" => $other_details,
                "emi_month" => $payment->emi_month,
                "interest" => $payment->interest,
                "created_by_name" => $payment->created_by_name,
                "updated_by_name" => $payment->updated_by_name,
                "created_at" => $payment->created_at,
                "updated_at" => $payment->updated_at,
            ];
            return [
                "order_id" => $payment->order_id,
                "amount" => $payment->amount,
                "transaction_type" => $payment->transaction_type,
                "method" => $payment->method,
                "method_details" => $digital_payment_details,
                "emi_month" => $payment->emi_month,
                "interest" => $payment->interest,
                "created_by_name" => $payment->created_by_name,
                "updated_by_name" => $payment->updated_by_name,
                "created_at" => $payment->created_at,
                "updated_at" => $payment->updated_at,
            ];
        });
        return $payments->toArray();
    }

    private function generatePartnerPosOrderDiscountsMigrationData()
    {
        $pos_order_discounts =  DB::table('pos_order_discounts')->whereIn('pos_order_discounts.pos_order_id', $this->partnerPosOrderIds)
            ->leftJoin('pos_order_items', 'pos_order_items.id', '=', 'pos_order_discounts.item_id')
            ->select('pos_order_discounts.pos_order_id AS order_id', DB::raw('(CASE 
                        WHEN pos_order_discounts.type = "voucher" THEN "voucher"
                        WHEN pos_order_discounts.type = "service" THEN "order_sku"
                        ELSE "order"  
                        END) AS type'), 'pos_order_discounts.amount', 'pos_order_discounts.original_amount',
                'pos_order_discounts.is_percentage', 'pos_order_discounts.cap', 'pos_order_discounts.item_id AS type_id',
                'pos_order_discounts.created_by_name', 'pos_order_discounts.updated_by_name',
                DB::raw('SUBTIME(pos_order_discounts.created_at,"6:00:00") as created_at, SUBTIME(pos_order_discounts.updated_at,"6:00:00") as updated_at'), 'pos_order_items.service_id AS sku_id')
            ->get();

        if(!is_array($pos_order_discounts)) {
            $discounts = $pos_order_discounts->toArray();
        } else {
            $discounts = $pos_order_discounts;
        }
        $service_ids = array_column($discounts, 'sku_id');
        $sku_ids = $this->getSkuIdsForProducts($service_ids);
        $skus = $sku_ids['skus'];
        $pos_order_discounts = collect($pos_order_discounts);
        $pos_order_discounts->each(function ($discount, $key) use ($skus) {
            $discount->sku_id = $skus[$discount->sku_id] ?? null;
        });
        return $pos_order_discounts;
    }

    private function generatePosOrderLogsMigrationData()
    {
        $logs =  PosOrderLog::with(['order' => function ($pos_order) {
            $pos_order->withTrashed()->select('id');
        }])->whereIn('pos_order_id', $this->partnerPosOrderIds)->select('id','type', 'pos_order_id', 'log', 'details')->get();

        $data = collect();
        collect($logs)->each(function ($pos_order_logs) use(&$data) {
            $temp = new stdClass();
            $temp->order_id = $pos_order_logs->pos_order_id;
            $temp->old_value = json_encode([
                "log" => $pos_order_logs->log,
                "previous_order_id" => $pos_order_logs->order->previous_order_id ?: null
            ],true);
            $temp->new_value = json_encode($pos_order_logs->details,true);
            $data->push($temp);
        });
        return $data;
    }

    public function generatePosCustomersData()
    {
        return DB::table('partner_pos_customers')
            ->where('partner_id', $this->partner->id)
            ->leftJoin('pos_customers', 'partner_pos_customers.customer_id', '=', 'pos_customers.id')
            ->leftJoin('profiles', 'pos_customers.profile_id', '=', 'profiles.id')
            ->select('partner_pos_customers.customer_id as id', 'partner_pos_customers.partner_id', 'profiles.name',
                'profiles.mobile', 'profiles.email', 'profiles.pro_pic',
                DB::raw('SUBTIME(profiles.created_at,"6:00:0") as created_at, 
                SUBTIME(profiles.updated_at,"6:00:0") as updated_at'))
            ->get();
    }

    private function getSkuIdsForProducts($productIds)
    {
        $data = [];
        $data['product_ids'] = $productIds;
        return $this->inventoryServerClient->post('api/v1/get-skus-by-product-ids', $data);
    }

    private function getPosOrderRelatedData($orderIds, $posCustomerIds): array
    {
        $pos_order_items = $this->posOrderItems->whereIn('order_id', $orderIds)->values()->toArray();
        $pos_order_payments = $this->posOrderPayments->whereIn('order_id', $orderIds)->values()->toArray();
        $pos_order_discounts = $this->posOrderDiscounts->whereIn('order_id', $orderIds)->values()->toArray();
        $pos_order_logs = $this->posOrderLogs->whereIn('order_id', $orderIds)->values()->toArray();
        $pos_customers = $this->posCustomers->whereIn('id', $posCustomerIds)->values()->toArray();
        return [$pos_order_items, $pos_order_payments, $pos_order_discounts, $pos_order_logs, $pos_customers];
    }

    private function setRedisKey()
    {
        $count = (int)Redis::get('PosOrderDataMigrationCount::' . $this->queue_and_connection_name);
        $count ? $count++ : $count = 1;
        Redis::set('PosOrderDataMigrationCount::' . $this->queue_and_connection_name, $count);
        Redis::set('DataMigration::Partner::' . $this->partner->id . '::Chunk::Queue::' . $this->currentChunk . '::PosOrder::Queue::' . $this->currentQueue, 'initiated');
    }

    private function increaseCurrentQueueValue()
    {
        $this->currentQueue += 1;
    }

}