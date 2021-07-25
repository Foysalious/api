<?php


namespace App\Sheba\SmanagerUserService;


class SmanagerUserService
{
    private $partnerId;
    private $customerId;
    /**
     * @var SmanagerUserServerClient
     */
    private $smanagerUserServerClient;

    public function __construct(SmanagerUserServerClient $smanagerUserServerClient)
    {
        $this->smanagerUserServerClient = $smanagerUserServerClient;
    }

    /**
     * @param mixed $partnerId
     * @return SmanagerUserService
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $customerId
     * @return SmanagerUserService
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function show()
    {
        $customer_info = $this->getCustomerInfoFromSmanagerUserService();

    }

    private function getCustomerInfoFromSmanagerUserService()
    {
        $this->smanagerUserServerClient->get('api/v1/partners/'.$this->partnerId.'/users/'.$this->customerId);
    }

}