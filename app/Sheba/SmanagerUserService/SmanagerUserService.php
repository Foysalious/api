<?php namespace App\Sheba\SmanagerUserService;


use App\Sheba\PosOrderService\PosOrderServerClient;
use Carbon\Carbon;

class SmanagerUserService
{
    private $partnerId;
    private $customerId;
    /**
     * @var SmanagerUserServerClient
     */
    private $smanagerUserServerClient;
    /**
     * @var PosOrderServerClient
     */
    private $posOrderServerClient;

    public function __construct(SmanagerUserServerClient $smanagerUserServerClient, PosOrderServerClient $posOrderServerClient)
    {
        $this->smanagerUserServerClient = $smanagerUserServerClient;
        $this->posOrderServerClient = $posOrderServerClient;
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

    /**
     * @return array
     */
    public function getDetails()
    {
        $customer_info = $this->getCustomerInfoFromSmanagerUserService();
        list($total_purchase_amount,$total_used_promo) = $this->getPurchaseAmountAndTotalUsedPromo();
        $customer_details = [];
        $customer_details['id'] = isset($customer_info['_id']) ? $customer_info['_id'] : null;
        $customer_details['name'] = isset($customer_info['name']) ? $customer_info['name'] : null;
        $customer_details['phone'] = isset($customer_info['phone']) ? $customer_info['phone'] : null;
        $customer_details['email'] = isset($customer_info['email']) ? $customer_info['email'] : null;
        $customer_details['address'] = isset($customer_info['address']) ? $customer_info['address'] : null;
        $customer_details['image'] = isset($customer_info['pro_pic']) ? $customer_info['pro_pic'] : null;
        $customer_details['customer_since'] = isset($customer_info['created_at']) ? $customer_info['created_at'] : null;
        $customer_details['customer_since_formatted'] = isset($customer_info['created_at']) ? Carbon::parse($customer_info['created_at'])->diffForHumans(): null;
        $customer_details['total_purchase_amount'] = $total_purchase_amount;
        $customer_details['total_used_promo'] = $total_used_promo;
      /*  $customer_details['total_due_amount'] = $this->getTotalDueAmount();
        $customer_details['total_payable_amount'] = $this->getTotalPurchaseAmount();*/
        $customer_details['is_customer_editable'] = true;
        $customer_details['note'] =  isset($customer_info['note']) ? $customer_info['note'] : null;
        $customer_details['is_supplier'] =  isset($customer_info['is_supplier']) ? $customer_info['is_supplier'] : null;

        return $customer_details;
    }

    /**
     * @return mixed
     */
    private function getCustomerInfoFromSmanagerUserService()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/'.$this->partnerId.'/pos-users/'.$this->customerId);
    }

    /**
     * @return array
     */
    private function getPurchaseAmountAndTotalUsedPromo()
    {
        $response = $this->posOrderServerClient->get('api/v1/customers/'.$this->customerId.'/order-amount');
        return [$response['total_purchase_amount'],$response['total_used_promo']];
    }

    private function getTotalDueAmountAndPayableAmount()
    {

    }

}