<?php namespace Sheba\CustomerDeliveryAddress;

use App\Exceptions\HyperLocationNotFoundException;
use App\Models\Customer;
use App\Models\HyperLocal;
use Sheba\Location\Geo;
use Sheba\Repositories\Interfaces\Customer\CustomerDeliveryAddressInterface;

class Creator
{
    private $customerDeliveryAddressRepository;
    private $data;
    private $name;
    private $houseNo;
    private $roadNo;
    private $blockNo;
    private $sectorNo;
    private $city;
    private $addressText;
    /** @var Customer */
    private $customer;
    /** @var Geo */
    private $geo;
    private $isSave;

    public function __construct(CustomerDeliveryAddressInterface $customer_delivery_address_repository)
    {
        $this->customerDeliveryAddressRepository = $customer_delivery_address_repository;
        $this->isSave = 1;
    }

    public function setHouseNo($house_no)
    {
        $this->houseNo = $house_no;
        return $this;
    }

    public function setRoadNo($road_no)
    {
        $this->roadNo = $road_no;
        return $this;
    }

    public function setBlockNo($block_no)
    {
        $this->blockNo = $block_no;
        return $this;
    }

    public function setSectorNo($sector_no)
    {
        $this->sectorNo = $sector_no;
        return $this;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function setAddressText($address_text)
    {
        $this->addressText = $address_text;
        return $this;
    }

    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setIsSave($is_save)
    {
        $this->isSave = $is_save;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     * @throws HyperLocationNotFoundException
     */
    public function create()
    {
        $this->makeData();
        $address = $this->customerDeliveryAddressRepository->create($this->data);
        if (!$this->isSave) $address->delete();
        return $address;
    }

    /**
     * @throws HyperLocationNotFoundException
     */
    private function makeData()
    {
        $hyper_local = HyperLocal::insidePolygon($this->geo->getLat(), $this->geo->getLng())->first();
        if (!$hyper_local) throw new HyperLocationNotFoundException('Your are out of service area.');
        $this->data = [
            'customer_id' => $this->customer->id,
            'address' => $this->addressText,
            'road_no' => $this->roadNo,
            'house_no' => $this->houseNo,
            'block_no' => $this->blockNo,
            'sector_no' => $this->sectorNo,
            'city' => $this->city,
            'name' => $this->name ? $this->name : $this->customer->profile->name,
            'mobile' => $this->customer->profile->mobile,
            'geo_informations' => json_encode(['lat' => $this->geo->getLat(), 'lng' => $this->geo->getLng()]),
            'location_id' => $hyper_local->location_id
        ];
    }
}