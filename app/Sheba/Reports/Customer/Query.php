<?php namespace Sheba\Reports\Customer;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Sheba\Reports\Query as BaseQuery;

class Query extends BaseQuery
{
    /** @var bool */
    private $isAdvanced;

    /**
     * @param bool $is_advanced
     * @return $this
     */
    public function setIsAdvanced($is_advanced)
    {
        $this->isAdvanced = $is_advanced;
        return $this;
    }

    /**
     * @return Builder
     */
    public function build()
    {
        return $this->normalQuery();
    }

    private function normalQuery()
    {
        if(!$this->isAdvanced) return Customer::with('profile');

        // Customer::join('profiles', 'customers.profile_id', '=', 'profiles.id')
        //->with('orders.location', 'orders.partnerOrders.jobs.usedMaterials', 'orders.partnerOrders.jobs.service')

        return Customer::with('profile', 'orders.location', 'orders.partnerOrders.jobs.usedMaterials',
            'orders.partnerOrders.jobs.service');
    }

    private function optimizedQuery()
    {

    }
}