<?php namespace Sheba\Reports\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use Sheba\Reports\ReportData;

abstract class CustomerData extends ReportData
{
    /** @var Request $request */
    protected $request;

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function get()
    {
        return $this->getCustomers($this->calculateTimeFrame());
    }

    abstract protected function calculateTimeFrame();

    /**
     * @param $time_frame
     * @param $customer_ids
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Collection|static|static[]
     */
    protected function getCustomers($time_frame = null, $customer_ids = [])
    {
        $is_advanced = $this->request->has('is_advanced') && $this->request->is_advanced == "on";

        #$customer_base_query = Customer::join('profiles', 'customers.profile_id', '=', 'profiles.id')->with('orders.location', 'orders.partnerOrders.jobs.usedMaterials', 'orders.partnerOrders.jobs.service');

        if ($is_advanced) {
            $customer_base_query = Customer::with('profile', 'orders.location', 'orders.partnerOrders.jobs.usedMaterials', 'orders.partnerOrders.jobs.service');
        } else {
            $customer_base_query = Customer::with('profile');
        }

        $customers = clone $customer_base_query;
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
            $rem_customers = clone $customer_base_query;
            $rem_customers = $rem_customers->whereNotIn('id', $customers->pluck('id')->toArray())->get();
            $filtered_from_rem_customers = $rem_customers->filter(function ($item) {
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
            $customers = $customers->filter(function ($customer) {
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
            $customers = $customers->map(function ($customer) {
                $customer->orders->map(function ($order) {
                    return $order->calculate($price_only = true);
                });
                return $customer;
            });
        }

        return $customers;
    }
}