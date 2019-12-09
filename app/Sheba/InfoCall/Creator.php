<?php namespace Sheba\InfoCall;

use App\Models\Customer;
use Carbon\Carbon;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;
    private $name;
    private $mobile;
    /** @var Customer */
    private $customer;
    private $serviceName;
    private $estimatedBudget;
    private $locationId;

    /**
     * @param mixed $name
     * @return Creator
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $mobile
     * @return Creator
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }


    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param mixed $serviceName
     * @return Creator
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /**
     * @param mixed $estimatedBudget
     * @return Creator
     */
    public function setEstimatedBudget($estimatedBudget)
    {
        $this->estimatedBudget = $estimatedBudget;
        return $this;
    }

    /**
     * @param mixed $locationId
     * @return Creator
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
        return $this;
    }

    public function create()
    {
        $data = [
            'service_name' => $this->serviceName,
            'estimated_budget' => $this->estimatedBudget,
            'customer_name' => $this->name,
            'location_id' => $this->locationId,
            'customer_mobile' => $this->mobile,
            'customer_email' => $this->customer->profile->email,
            'customer_address' => $this->customer->profile->address,
            'follow_up_date' => Carbon::now()->addMinutes(30),
            'intended_closing_date' => Carbon::now()->addMinutes(30)
        ];
        $this->setModifier($this->customer);
        
        return $this->customer->infoCalls()->create($this->withCreateModificationField($data));
    }
}
