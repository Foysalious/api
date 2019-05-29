<?php namespace Sheba\Reports\Customer;

use App\Http\Requests\Reports\ReportTimeLine;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\ReportData;

abstract class CustomerData extends ReportData
{
    /** @var ReportTimeLine & Request $request */
    protected $request;

    /** @var Presenter */
    protected $presenter;
    /** @var Query */
    protected $query;

    public function __construct(Presenter $presenter, Query $query)
    {
        $this->presenter = $presenter;
        $this->query = $query;
    }

    public function setRequest(ReportTimeLine $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $this->presenter->setIsAdvanced($this->isAdvanced());
        $customers = $this->getCustomers($this->request->getTimeLine());
        return $customers->map(function (Customer $customer) {
            return $this->presenter->setCustomer($customer)->getForView();
        })->toArray();
    }

    protected function isAdvanced()
    {
        return $this->request->has('is_advanced') &&
            ($this->request->is_advanced == "on" || $this->request->is_advanced == true || $this->request->is_advanced == 1);
    }

    /**
     * @param $time_frame
     * @param $customer_ids
     * @return Collection
     */
    protected function getCustomers($time_frame = null, $customer_ids = [])
    {
        $is_advanced = $this->isAdvanced();
        $this->query->setIsAdvanced($is_advanced);
        $customers = $this->query->build();
        if (!empty($customer_ids)) {
            $customers = $customers->whereIn('id', $customer_ids);
        }
        if ($this->request->has('locations') && $is_advanced) {
            $customers = $customers->locations($this->request->locations);
        }
        if ($this->request->has('operators')) {
            $customers = $customers->operators($this->request->operators);
        }
        if (!empty($time_frame)) {
            $customers = $this->notLifetimeQuery($customers, $time_frame);
        }

        $customers = $customers->get();

        if ($this->request->has('locations') && empty($customer_ids) && $is_advanced) {
            $rem_customers = $this->query->build();
            $rem_customers = $rem_customers->whereNotIn('id', $customers->pluck('id')->toArray())->get();
            $filtered_from_rem_customers = $rem_customers->filter(function (Customer $item) {
                if ($this->request->has('operators') && !in_array(substr($item->mobile, 0, 6), $this->request->operators)) {
                    return false;
                }

                $order_locations = $item->orderLocationWithCounts()->keys();
                foreach ($this->request->locations as $location) {
                    if ($order_locations->contains($location)) {
                        return true;
                    }
                }
                return false;
            });
            $customers = $customers->merge($filtered_from_rem_customers);
        }

        if ($this->request->has('order_count') && empty($customer_ids) && $is_advanced) {
            $customers = $customers->filter(function ($customer) {
                if ($this->request->order_count[0] == ">") {
                    return $customer->orders->count() > intval(substr($this->request->order_count, 1));
                }

                return $customer->orders->count() == $this->request->order_count;
            });
        }

        if ($this->request->has('channels') && $is_advanced) {
            $customers = $customers->filter(function (Customer $customer) {
                $channels = $customer->orderChannelWithCounts()->keys();
                foreach ($this->request->channels as $location) {
                    if ($channels->contains($location)) {
                        return true;
                    }
                }
                return false;
            });
        }

        if ($is_advanced) {
            $customers = $customers->map(function (Customer $customer) {
                $customer->orders->map(function (Order $order) {
                    return $order->calculate($price_only = true);
                });
                return $customer;
            });
        }

        return $customers;
    }
}