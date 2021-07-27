<?php namespace App\Sheba\SmanagerUserService;


use App\Sheba\PosOrderService\PosOrderServerClient;
use Carbon\Carbon;
use PhpParser\Node\Scalar\String_;

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
    private $note;
    private $name;
    private $gender;
    private $bloodGroup;
    private $pic;
    private $bnName;
    private $mobile;
    private $address;
    private $email;
    private $dob;

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

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setBnName($bnName)
    {
        $this->bnName = $bnName;
        return $this;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    public function setBloodGroup($bloodGroup)
    {
        $this->bloodGroup = $bloodGroup;
        return $this;
    }

    public function setDob($dob)
    {
        $this->dob = $dob;
        return $this;
    }

    public function setproPic($pic)
    {
        $this->pic = $pic;
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
        // list($total_purchase_amount,$total_used_promo) = $this->getPurchaseAmountAndTotalUsedPromo();
        $customer_details = [];
        $customer_details['id'] = isset($customer_info['_id']) ? $customer_info['_id'] : null;
        $customer_details['name'] = isset($customer_info['name']) ? $customer_info['name'] : null;
        $customer_details['phone'] = isset($customer_info['phone']) ? $customer_info['phone'] : null;
        $customer_details['email'] = isset($customer_info['email']) ? $customer_info['email'] : null;
        $customer_details['address'] = isset($customer_info['address']) ? $customer_info['address'] : null;
        $customer_details['image'] = isset($customer_info['pro_pic']) ? $customer_info['pro_pic'] : null;
        $customer_details['customer_since'] = isset($customer_info['created_at']) ? $customer_info['created_at'] : null;
        $customer_details['customer_since_formatted'] = isset($customer_info['created_at']) ? Carbon::parse($customer_info['created_at'])->diffForHumans() : null;
        /*  $customer_details['total_purchase_amount'] = $total_purchase_amount;
          $customer_details['total_used_promo'] = $total_used_promo;
          $customer_details['total_due_amount'] = $this->getTotalDueAmount();
          $customer_details['total_payable_amount'] = $this->getTotalPurchaseAmount();*/
        $customer_details['is_customer_editable'] = true;
        $customer_details['note'] = isset($customer_info['note']) ? $customer_info['note'] : null;
        $customer_details['is_supplier'] = isset($customer_info['is_supplier']) ? $customer_info['is_supplier'] : null;

        return $customer_details;
    }

    public function showCustomerListByPartnerId()
    {
        return $this->getCustomerListByPartnerId();

    }

    public function makeCreateData()
    {
        return [
            'note' => $this->note,
            'name' => $this->name,
            'bn_name' => $this->bnName,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'address' => $this->address,
            'gender' => $this->gender,
            'blood_group' => $this->bloodGroup,
            'dob' => $this->dob,
            'pro_pic' => $this->pic,
        ];
    }

    public function storePosCustomer()
    {
        $data = $this->makeCreateData();
        return $this->smanagerUserServerClient->post('api/v1/partners/' . $this->partnerId, $data);
    }

    public function makeUpdateData()
    {

        $data = [];

        if (isset($this->pic)) $data['pro_pic'] = $this->pic;
        if (isset($this->dob)) $data['dob'] = $this->dob;
        if (isset($this->bloodGroup)) $data['blood_group'] = $this->bloodGroup;
        if (isset($this->gender)) $data['gender'] = $this->gender;
        if (isset($this->address)) $data['address'] = $this->address;
        if (isset($this->email)) $data['email'] = $this->email;
        if (isset($this->bnName)) $data['bn_name'] = $this->bnName;
        if (isset($this->mobile)) $data['mobile'] = $this->mobile;
        if (isset($this->name)) $data['name'] = $this->name;
        if (isset($this->note)) $data['note'] = $this->note;
        if (isset($this->email)) $data['email'] = $this->email;


        return $data;
    }

    public function updatePosCustomer()
    {
        $data = $this->makeUpdateData();
        return $this->smanagerUserServerClient->put('api/v1/partners/' . $this->partnerId.'/pos-users/'.$this->customerId, $data);
    }

    /**
     * @return mixed
     */
    private function getCustomerInfoFromSmanagerUserService()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partnerId . '/pos-users/' . $this->customerId);
    }

    /**
     * @return array
     */
    private function getPurchaseAmountAndTotalUsedPromo()
    {
        $response = $this->posOrderServerClient->get('v1/customers/purchase-amount-and-used-promo');
        return [$response['total_purchase_amount'], $response['total_used_promo']];
    }

    private function getTotalDueAmount()
    {

    }

    private function getTotalPurchaseAmount()
    {

    }

    private function getCustomerListByPartnerId()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partnerId);
    }

}
