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

    public function show()
    {
        $customer_info = $this->getCustomerInfoFromSmanagerUserService();
        $customer_details = [];
        $customer_details['id'] = isset($customer_info['name']) ? $customer_info['name'] : null;
        $customer_details['name'] = isset($customer_info['name']) ? $customer_info['name'] : null;
        $customer_details['phone'] = isset($customer_info['phone']) ? $customer_info['phone'] : null;
        $customer_details['email'] = isset($customer_info['email']) ? $customer_info['email'] : null;
        $customer_details['address'] = isset($customer_info['address']) ? $customer_info['address'] : null;
        $customer_details['image'] = isset($customer_info['pro_pic']) ? $customer_info['pro_pic'] : null;
        $customer_details['customer_since'] = isset($customer_info['created_at']) ? $customer_info['created_at'] : null;
        $customer_details['customer_since_formatted'] = isset($customer_info['created_at']) ? $customer_info['created_at']->diffForHumans(): null;
        $customer_details['total_purchase_amount'] = '';
        $customer_details['total_due_amount'] = '';
        $customer_details['total_used_promo'] = '';
        $customer_details['total_payable_amount'] = '';
        $customer_details['is_customer_editable'] = true;
        $customer_details['note'] =  isset($customer_info['note']) ? $customer_info['note'] : null;
        $customer_details['is_supplier'] =  isset($customer_info['is_supplier']) ? $customer_info['is_supplier'] : null;

        return $customer_details;
    }

    private function getCustomerInfoFromSmanagerUserService()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/'.$this->partnerId.'/pos-users/'.$this->customerId);
    }

}