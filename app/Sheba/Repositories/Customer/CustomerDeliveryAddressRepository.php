<?php namespace Sheba\Repositories\Customer;


use App\Models\CustomerDeliveryAddress;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\Customer\CustomerDeliveryAddressInterface;

class CustomerDeliveryAddressRepository extends BaseRepository implements CustomerDeliveryAddressInterface
{
    public function __construct(CustomerDeliveryAddress $address)
    {
        parent::__construct();
        $this->setModel($address);
    }
}