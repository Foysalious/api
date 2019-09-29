<?php namespace Sheba\Repositories;

use App\Models\Customer;
use App\Models\CustomerTransaction;
use Carbon\Carbon;

class CustomerTransactionRepository
{
    private $customer;

    function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param $data
     * @param null $tags
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \Exception
     */
    public function save($data, $tags = null)
    {
        $transaction = null;
        if ($data['amount'] > 0) {
            $data['created_at'] = Carbon::now();
            $transaction = $this->customer->transactions()->save(new CustomerTransaction($data));
            (new CustomerRepository())->updateWallet($this->customer, $data['amount'], $data['type']);
            // if (is_array($tags) && !empty($tags[0])) $transaction->tags()->sync($tags);
        }
        return $transaction;
    }
}