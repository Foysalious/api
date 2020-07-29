<?php namespace Sheba\Order;

use App\Exceptions\HyperLocationNotFoundException as HyperLocationNotFoundException;
use App\Exceptions\NotFoundException;
use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Models\Job;
use App\Models\Partner;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Customer\Creator as CustomerCreator;
use Sheba\CustomerDeliveryAddress\Creator as CustomerDeliveryAddressCreator;
use Sheba\Jobs\AcceptJobAndAssignResource;
use Sheba\Location\Geo;
use Sheba\Order\Creator as OrderCreator;
use Sheba\ServiceRequest\Exception\ServiceIsUnpublishedException;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\ServiceRequest\ServiceRequestObject;

class OrderCreateRequest
{
    /** @var Geo */
    protected $geo;
    protected $date;
    protected $time;
    protected $partnerId;
    /** @var OrderCreateRequestPolicy */
    protected $policy;
    /** @var Resource */
    protected $resource;
    /** @var Response */
    protected $response;
    protected $mobile;
    protected $name;
    /** @var CustomerCreator */
    protected $customerCreator;
    /** @var CustomerDeliveryAddressCreator */
    protected $deliveryAddressCreator;
    protected $address;
    /** @var Creator */
    protected $orderCreator;
    protected $additionalInformation;
    protected $services;
    /** @var ServiceRequest */
    protected $serviceRequest;
    protected $salesChannel;
    protected $paymentMethod;
    protected $assignResource;
    private $job;
    private $partner;
    /**
     * @var AcceptJobAndAssignResource
     */
    protected $acceptJobAndAssignResource;
    /**
     * @var Request
     */
    protected $request;

    public function __construct(OrderCreateRequestPolicy $policy, Response $response, CustomerCreator $customerCreator,
                                CustomerDeliveryAddressCreator $deliveryAddressCreator, OrderCreator $orderCreator, ServiceRequest $serviceRequest,
                                AcceptJobAndAssignResource $acceptJobAndAssignResource)
    {
        $this->policy = $policy;
        $this->response = $response;
        $this->customerCreator = $customerCreator;
        $this->deliveryAddressCreator = $deliveryAddressCreator;
        $this->orderCreator = $orderCreator;
        $this->serviceRequest = $serviceRequest;
        $this->acceptJobAndAssignResource = $acceptJobAndAssignResource;
    }

    /**
     * @param Geo $geo
     * @return OrderCreateRequest
     */
    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    /**
     * @param $services
     * @return OrderCreateRequest
     */
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @param $date
     * @return OrderCreateRequest
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param $time
     * @return OrderCreateRequest
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @param $id
     * @return OrderCreateRequest
     */
    public function setPartnerId($id)
    {
        $this->partnerId = $id;
        return $this;
    }

    /**
     * @param Resource $resource
     * @return OrderCreateRequest
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param $mobile
     * @return OrderCreateRequest
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @param $name
     * @return OrderCreateRequest
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $address
     * @return OrderCreateRequest
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param $info
     * @return OrderCreateRequest
     */
    public function setAdditionalInformation($info)
    {
        $this->additionalInformation = $info;
        return $this;
    }

    /**
     * @param $salesChannel
     * @return OrderCreateRequest
     */
    public function setSalesChannel($salesChannel)
    {
        $this->salesChannel = $salesChannel;
        return $this;
    }

    /**
     * @param $paymentMethod
     * @return OrderCreateRequest
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @param $assignResource
     * @return OrderCreateRequest
     */
    public function setAssignResource($assignResource)
    {
        $this->assignResource = $assignResource;
        return $this;
    }

    /**
     * @param Request $request
     * @return OrderCreateRequest
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Response
     * @throws DestinationCitySameAsPickupException
     * @throws HyperLocationNotFoundException
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws NotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     * @throws ServiceIsUnpublishedException
     * @throws ValidationException
     */
    public function create()
    {
        if ($this->canCreate()) {
            $response = $this->orderCreator->setServices($this->services)->setCustomer($this->getCustomer())->setMobile($this->mobile)
                ->setDate($this->date)->setTime($this->time)->setAddressId($this->getDeliveryAddress()->id)->setAdditionalInformation($this->additionalInformation)
                ->setPartnerId($this->partnerId)->setSalesChannel($this->salesChannel)->setPaymentMethod($this->paymentMethod)->create();
            $this->response->setResponse($response);
        }
        if ($this->assignResource && $this->response->hasSuccess()) {
            $this->job = Job::find($this->response->getResponse()->job_id);
            $this->partner = Partner::find($this->partnerId);
            $this->acceptJobAndAssignResource();
        }
        return $this->response;
    }

    /**
     * @return bool
     * @throws DestinationCitySameAsPickupException
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws NotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     * @throws ServiceIsUnpublishedException
     * @throws ValidationException
     */
    private function canCreate()
    {
        $this->policy->setGeo($this->geo)->setServiceRequestObject($this->getServiceRequestObject())->setDate($this->date)->setTime($this->time)->setPartnerId($this->partnerId);
        if ($this->assignResource) $this->policy->setResource($this->resource);
        return $this->policy->canCreate();
    }

    private function getCustomer()
    {
        return $this->customerCreator->setMobile($this->mobile)->setName($this->name)->create();
    }

    /**
     * @return Model
     * @throws HyperLocationNotFoundException
     */
    private function getDeliveryAddress()
    {
        return $this->deliveryAddressCreator->setCustomer($this->getCustomer())->setAddressText($this->address)->setGeo($this->geo)->setName($this->getCustomer()->profile->name)->create();
    }

    /**
     * @return ServiceRequestObject[]
     * @throws DestinationCitySameAsPickupException
     * @throws InsideCityPickUpAddressNotFoundException
     * @throws OutsideCityPickUpAddressNotFoundException
     * @throws ValidationException
     * @throws ServiceIsUnpublishedException
     */
    private function getServiceRequestObject()
    {
        return $this->serviceRequest->setServices(json_decode($this->services, 1))->get();
    }

    private function acceptJobAndAssignResource()
    {
        $this->acceptJobAndAssignResource->setJob($this->job)->setPartner($this->partner)->setResource($this->resource)->setRequest($this->request)->acceptJobAndAssignResource();
    }
}