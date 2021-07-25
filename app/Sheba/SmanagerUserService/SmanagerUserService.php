<?php namespace App\Sheba\SmanagerUserService;


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
/*
 "id": 565,
        "name": "Shovan Chowdhury",
        "phone": "+8801674558806",
        "email": "shovancse0918@gmail.com",
        "address": null,
        "image": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg",
        "customer_since": "2020-05-19",
        "customer_since_formatted": "1 year ago",
        "total_purchase_amount": 214105.71,
        "total_due_amount": 202342.7,
        "total_used_promo": 0,
        "total_payable_amount": 0,
        "is_customer_editable": true,
        "note": "",
        "is_supplier": 0
 */
    public function show()
    {
        $customer_info = $this->getCustomerInfoFromSmanagerUserService();
        $customer_details = [];
        $customer_details['id'] = $customer_info['_id'];
        $customer_details['name'] = $customer_info['_id'];
        $customer_details['phone'] = $customer_info['_id'];
        $customer_details['email'] = $customer_info['_id'];

    }

    private function getCustomerInfoFromSmanagerUserService()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/'.$this->partnerId.'/users/'.$this->customerId);
    }

}