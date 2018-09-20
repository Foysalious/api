<?php namespace Sheba\Repositories;

use App\Models\Customer;
use Carbon\Carbon;

class CustomerRepository extends BaseRepository
{
    public function update(Customer $customer, $data)
    {
        $customer->update($this->withUpdateModificationField($data));
    }

    public function getTodayRegisteredCustomers()
    {
        return $this->getRegisteredCustomersOf(Carbon::today());
    }

    public function countTodayRegisteredCustomers()
    {
        return $this->countRegisteredCustomersOf(Carbon::today());
    }

    public function getRegisteredCustomersOf(Carbon $date)
    {
        return $this->registeredCustomersByDateQuery($date)->get();
    }

    public function countRegisteredCustomersOf(Carbon $date)
    {
        return $this->registeredCustomersByDateQuery($date)->count();
    }

    private function registeredCustomersByDateQuery(Carbon $date)
    {
        return Customer::whereDate('created_at', '=', $date->toDateString());
    }

    /**
     * @param Customer $customer
     * @param $amount
     * @param $type
     * @throws \Exception
     */
    public function updateWallet(Customer $customer, $amount, $type)
    {
        $new_wallet = ($type == 'Debit') ? ($customer->wallet - $amount) : ($customer->wallet + $amount);
        $this->update($customer, ['wallet' => $new_wallet]);
    }

    /**
     * @param Customer $customer
     * @param $point
     */
    public function updateRewardPoint(Customer $customer, $point)
    {
        $new_reward_point = $customer->reward_point + $point;
        $this->update($customer, ['reward_point' => $new_reward_point]);
    }
}