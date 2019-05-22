<?php namespace Sheba\Reports\Customer;

use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;

class UniqueCustomerData extends CustomerData
{
    /**
     * @return array
     */
    public function get()
    {
        $this->presenter->setIsAdvanced($this->isAdvanced())->setIsUnique(true);
        $time_line = $this->request->getTimeLine();
        $start_date = Carbon::parse($time_line['start_date']);
        $end_date = Carbon::parse($time_line['end_date'])->addDay()->subSecond();

        $orders = $this->notLifetimeQuery(Order::query(), $time_line);
        $customers = $this->getCustomers(null, $orders->pluck('customer_id')->unique()->toArray());

        return $customers->map(function (Customer $customer) use ($start_date, $end_date) {
            $customer->is_returning = !($customer->created_at->between($start_date, $end_date));
            return $this->presenter->setCustomer($customer)->getForView();
        })->toArray();
    }
}