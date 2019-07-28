<?php namespace Sheba\Repositories;


use App\Models\Customer;
use Sheba\Repositories\Interfaces\CustomerRepositoryInterface;
use Sheba\Voucher\Creator\Referral;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $customer)
    {
        parent::__construct();
        $this->setModel($customer);
    }

    public function create(array $attributes)
    {
        $attributes['remember_token'] = str_random(255);
        $customer = $this->model->create($this->withCreateModificationField($attributes));
        $customer = $customer::find($customer->id);
        new Referral($customer);
        return $customer;
    }

}