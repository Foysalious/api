<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Checkout\ShebaOrderInterface;
use Sheba\Order\StatusCalculator;

class Order extends Model implements ShebaOrderInterface
{
    protected $guarded = ['id'];
    public $totalPrice;
    public $due;
    public $profit;
    private $statuses;

    public function __construct()
    {
        $this->statuses = constants('ORDER_STATUSES');
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
        if (in_array($this->sales_channel, ['Web', 'Call-Center', 'App', 'Facebook', 'App-iOS', 'E-Shop'])) {
            $prefix = 'D';
        } elseif ($this->sales_channel == 'B2B') {
            $prefix = 'F';
        } elseif ($this->sales_channel == 'Store') {
            $prefix = 'S';
        } else {
            $prefix = 'A';
        }
        return $prefix;
    }

    public function code()
    {
        $startFrom = 8000;
        return $this->channelCode() . '-' . sprintf('%06d', $this->id + $startFrom);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function updateLogs()
    {
        return $this->hasMany(OrderUpdateLog::class);
    }

    public function getVersion()
    {
        return $this->id > (int)env('LAST_ORDER_ID_V1') ? 'v2' : 'v1';
    }

    public function department()
    {
        return getSalesChannels('department')[$this->sales_channel];
    }

    public function isCancelled()
    {
        return $this->getStatus() == $this->statuses['Cancelled'];
    }

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
}
