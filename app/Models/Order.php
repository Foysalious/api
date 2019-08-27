<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Checkout\ShebaOrderInterface;
use Sheba\Order\Code\Builder as CodeBuilder;
use Sheba\Order\StatusCalculator;
use Sheba\Portals\Portals;
use Sheba\Voucher\Contracts\CanHaveVoucher;
use Sheba\Report\Updater\Order as ReportUpdater;

class Order extends Model implements ShebaOrderInterface, CanHaveVoucher
{
    use ReportUpdater;
    protected $guarded = ['id'];
    public $totalPrice;
    public $due;
    public $profit;
    private $statuses;
    private $jobStatuses;
    private $salesChannelDepartments;
    private $salesChannelShortNames;

    /** @var CodeBuilder */
    private $codeBuilder;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->statuses = constants('ORDER_STATUSES');
        $this->jobStatuses = constants('JOB_STATUSES');
        $this->salesChannelDepartments = getSalesChannels('department');
        $this->salesChannelShortNames = getSalesChannels('short_name');
        $this->codeBuilder = new CodeBuilder();
    }

    public function jobs()
    {
        return $this->hasManyThrough(Job::class, PartnerOrder::class);
    }

    public function partnerOrders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function partner_orders()
    {
        return $this->hasMany(PartnerOrder::class);
    }

    public function subscription()
    {
        return $this->belongsTo(SubscriptionOrder::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function deliveryAddress()
    {
        return $this->hasOne(CustomerDeliveryAddress::class, 'id', 'delivery_address_id')->withTrashed();
    }

    public function calculate($price_only = false)
    {
        $this->totalPrice = 0;
        $this->due = 0;
        foreach ($this->partner_orders as $partnerOrder) {
            $partnerOrder->calculate($price_only);
            $this->totalPrice += $partnerOrder->grossAmount;
            $this->due += $partnerOrder->due;
            $this->profit += $partnerOrder->profit;
        }
        $this->status = $this->getStatus();
        return $this;
    }

    public function getStatus()
    {
        return $this->isStatusCalculated() ? $this->status : (new StatusCalculator($this))->calculate();
    }

    private function isStatusCalculated()
    {
        return property_exists($this, 'status') && $this->status;
    }

    public function channelCode()
    {
        return $this->codeBuilder->channel($this);
    }

    public function code()
    {
        return $this->codeBuilder->order($this);
    }

    public function department()
    {
        return $this->salesChannelDepartments[$this->sales_channel];
    }

    public function shortChannel()
    {
        return $this->salesChannelShortNames[$this->sales_channel];
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class);
    }

    public function updateLogs()
    {
        return $this->hasMany(OrderUpdateLog::class);
    }

    public function getVersion()
    {
        return $this->id > (int)env('LAST_ORDER_ID_V1') ? 'v2' : 'v1';
    }

    public function isCancelled()
    {
        return $this->getStatus() == $this->statuses['Cancelled'];
    }

    /**
     * @return Job
     */
    public function lastJob()
    {
        if ($this->isCancelled()) return $this->jobs->last();
        return $this->jobs->filter(function ($job) {
            return $job->status != $this->jobStatuses['Cancelled'];
        })->first();
    }

    public function lastPartnerOrder()
    {
        if ($this->isCancelled()) return $this->partnerOrders->last();
        return $this->partnerOrders->filter(function ($partner_order) {
            return is_null($partner_order->cancelled_at);
        })->first();
    }

    public function findDeliveryIdFromAddressString()
    {
        $customer_addresses = $this->customer->delivery_addresses();
        $address = $customer_addresses->where('address', $this->delivery_address)->first();
        if ($address) return $address->id;
        return $customer_addresses->first()->id;

    }

    /** @TODO Remove */
    public function getTempAddress()
    {
        $location = json_decode($this->location->geo_informations);
        $delivery_address = (new CustomerDeliveryAddress());
        $delivery_address->customer_id = $this->customer_id;
        $delivery_address->name = $this->delivery_name;
        $delivery_address->mobile = $this->delivery_mobile;
        $delivery_address->address = $this->delivery_address;
        $delivery_address->geo_informations = json_encode(["lat" => $location->lat, "lng" => $location->lng]);
        return $delivery_address;
    }

    public function isLogisticOrder()
    {
        return $this->lastJob()->needsLogistic();
    }

    public function isReadyToPick()
    {
        return $this->lastJob()->isReadyToPickable();
    }

    public function isProcessable()
    {
        return $this->lastJob()->isProcessable();
    }

    public function isServeable()
    {
        return $this->lastJob()->isServeable();
    }

    public function isPayable()
    {
        return $this->lastJob()->isPayable();
    }

    public function hasCustomerReturned()
    {
        return !$this->customer->getFirstOrder()->created_at->isSameDay($this->created_at);
    }

    public function isFromOfflineBondhu()
    {
        return $this->affiliation_id && $this->portal_name == Portals::ADMIN;
    }
}