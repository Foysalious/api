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

    public function getAddressesForOrderPlacement($customer_id)
    {
        return $this->model->where('customer_id', $customer_id)->where('is_saved', 1)->hasGeo()->whereHas('location', function ($q) {
            $q->hasPolygon();
        })->select('id', 'location_id', 'address', 'name', 'geo_informations', 'flat_no', 'flat_no', 'road_no', 'house_no', 'block_no', 'sector_no', 'city', 'street_address', 'landmark');
    }
}