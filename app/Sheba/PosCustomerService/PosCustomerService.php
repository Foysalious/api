<?php namespace App\Sheba\PosCustomerService;

use App\Models\Partner;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Null_;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\Pos\Repositories\PosCustomerRepository;

class PosCustomerService
{
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
    private $createdFrom;
    /**
     * @var PosCustomerRepository
     */
    private $posCustomerRepository;
    private $partner;
    private $supplier;
    private $supplierId;
    private $companyName;

    public function __construct(SmanagerUserServerClient $smanagerUserServerClient, PosOrderServerClient $posOrderServerClient, PosCustomerRepository $posCustomerRepository)
    {
        $this->smanagerUserServerClient = $smanagerUserServerClient;
        $this->posOrderServerClient = $posOrderServerClient;
        $this->posCustomerRepository = $posCustomerRepository;
    }

    /**
     * @param Partner $partner
     * @return PosCustomerService
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
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

    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
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
     * @return PosCustomerService
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * @param mixed $supplierId
     * @return PosCustomerService
     */
    public function setSupplierId($supplierId)
    {
        $this->supplierId = $supplierId;
        return $this;
    }

    /**
     * @param mixed $companyName
     * @return PosCustomerService
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
        return $this;
    }

    /**
     * @param mixed $createdFrom
     * @return PosCustomerService
     */
    public function setCreatedFrom($createdFrom)
    {
        $this->createdFrom = $createdFrom;
        return $this;
    }

    /**
     * @return array
     * @throws InvalidPartnerPosCustomer
     * @throws AccountingEntryServerError
     */
    public function getDetails(): array
    {
        $customer_info = $this->getCustomerInfoFromSmanagerUserService();
        list($total_purchase_amount, $total_used_promo) = $this->getPurchaseAmountAndTotalUsedPromo();
        $getDueAndPayableAmount = $this->getDueAndPayableAmount();
        $total_due_amount = $getDueAndPayableAmount['due'];
        $total_payable_amount = $getDueAndPayableAmount['payable'];
        $created_at = isset($customer_info['created_at']) ? Carbon::parse($customer_info['created_at']) : null;
        $customer_details = [];
        $customer_details['id'] = $customer_info['_id'] ?? null;
        $customer_details['name'] = $customer_info['name'] ?? null;
        $customer_details['phone'] = $customer_info['mobile'] ?? null;
        $customer_details['email'] = $customer_info['email'] ?? null;
        $customer_details['address'] = $customer_info['address'] ?? null;
        $customer_details['image'] = $customer_info['pro_pic'] ?? null;
        $customer_details['customer_since'] = $customer_info['created_at'] ?? null;
        $customer_details['customer_since_formatted'] = isset($customer_info['created_at']) ? convertTimezone($created_at)->format('Y-m-d H:i:s') : null;
        $customer_details['total_purchase_amount'] = $total_purchase_amount;
        $customer_details['total_used_promo'] = $total_used_promo;
        $customer_details['total_due_amount'] = $total_due_amount;
        $customer_details['total_payable_amount'] = $total_payable_amount;
        $customer_details['is_customer_editable'] = true;
        $customer_details['note'] = $customer_info['note'] ?? null;
        $customer_details['is_supplier'] = $customer_info['is_supplier'] ?? 0;
        $customer_details['is_customer_and_supplier'] = rand(0,1);

        return $customer_details;
    }

    public function getSupplierDetails()
    {
        return $this->getSupplierInfoFromSmanagerUserService();
    }

    public function getOrders()
    {
        return $this->posOrderServerClient->get('api/v1/partners/' . $this->partner->id . '/customers/' . $this->customerId . '/orders');
    }

    /**
     * @throws Exceptions\SmanagerUserServiceServerError
     */
    public function deleteUser()
    {
        $this->deleteCustomerFromSmanagerUserService();
        return true;
    }

    /**
     * @throws Exceptions\SmanagerUserServiceServerError
     */
    public function deleteSupplier(): bool
    {
        $this->deleteSupplierFromSmanagerUserService();
        return true;
    }

    /**
     * @throws Exceptions\SmanagerUserServiceServerError
     */
    private function deleteCustomerFromSmanagerUserService()
    {
        $this->smanagerUserServerClient->delete('api/v1/partners/' . $this->partner->id . '/pos-users/' . $this->customerId);
    }

    /**
     * @throws Exceptions\SmanagerUserServiceServerError
     */
    private function deleteSupplierFromSmanagerUserService()
    {
        $this->smanagerUserServerClient->delete('api/v1/partners/' . $this->partner->id . '/suppliers/' . $this->supplierId);
    }

    private function deleteCustomerFromPosOrderService()
    {
        try {
            $this->posOrderServerClient->delete('api/v1/partners/' . $this->partner->id . '/customers/' . $this->customerId);
        } catch (\Exception $e) {
            return true;
        }

    }

    private function deleteUserFromAccountingService()
    {
        try {
            $this->posCustomerRepository->deleteCustomerFromDueTracker($this->partner, $this->customerId);
        } catch (AccountingEntryServerError $e) {
            return true;
        }
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
            'is_supplier' => $this->supplier,
            'created_from' => $this->createdFrom ?: 'contact'
        ];
    }

    public function storePosCustomer()
    {
        $data = $this->makeCreateData();
        return $this->smanagerUserServerClient->post('api/v1/partners/' . $this->partner->id . '/pos-users', $data);
    }

    public function storeSupplier()
    {
        $data = $this->makeSupplierCreateData();
        return $this->smanagerUserServerClient->post('api/v1/partners/' . $this->partner->id . '/suppliers', $data);
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
        if (isset($this->supplier)) $data['is_supplier'] = $this->supplier;
        return $data;
    }

    public function makeSupplierUpdateData()
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
        if (isset($this->companyName)) $data['company_name'] = $this->companyName;
        return $data;
    }

    public function makeSupplierCreateData(): array
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
        if (isset($this->company_name)) $data['company_name'] = $this->companyName;
        if (isset($this->createdFrom)) $data['created_from'] = $this->createdFrom;
        return $data;
    }

    public function updatePosCustomer()
    {
        $data = $this->makeUpdateData();
        return $this->smanagerUserServerClient->put('api/v1/partners/' . $this->partner->id . '/pos-users/' . $this->customerId, $data);
    }

    public function updateSupplier()
    {
        $data = $this->makeSupplierUpdateData();
        return $this->smanagerUserServerClient->put('api/v1/partners/' . $this->partner->id . '/suppliers/' . $this->supplierId, $data);
    }

    /**
     * @return mixed
     */
    public function getCustomerInfoFromSmanagerUserService()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partner->id . '/pos-users/' . $this->customerId);
    }

    /**
     * @return mixed
     */
    public function getSupplierInfoFromSmanagerUserService()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partner->id . '/suppliers/' . $this->customerId);
    }

    /**
     * @return array
     */
    private function getPurchaseAmountAndTotalUsedPromo(): array
    {

        try {
            $response = $this->posOrderServerClient->get('api/v1/partners/' . $this->partner->id . '/customers/' . $this->customerId . '/purchase-amount-promo-usage');
            return [$response['data']['total_purchase_amount'], $response['data']['total_used_promo']];
        } catch (\Exception $exception) {
            return [null, null];
        }

    }

    /**
     * @throws InvalidPartnerPosCustomer
     * @throws AccountingEntryServerError
     */
    private function getDueAndPayableAmount(): array
    {
        $data = ['due' => null, 'payable' => null];
        try {
            return $this->posCustomerRepository->getDueAmountFromDueTracker($this->partner, $this->customerId);
        } catch (AccountingEntryServerError $e) {
            return $data;
        } catch (InvalidPartnerPosCustomer $e) {
            return $data;
        } catch (ExpenseTrackingServerError $e) {
            return $data;
        }
    }

    private function getCustomerListByPartnerId()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partner->id . '/pos-users');
    }

    public function getContactList($type)
    {
        $url = 'api/v1/partners/' . $this->partner->id . '/contacts';
        if($type) $url .= '?type=' . $type;
        return $this->smanagerUserServerClient->get($url);
    }
}
