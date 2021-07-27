<?php namespace App\Sheba\SmanagerUserService;

use App\Models\Partner;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Carbon\Carbon;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;
use Sheba\Pos\Repositories\PosCustomerRepository;

class SmanagerUserService
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
    /**
     * @var PosCustomerRepository
     */
    private $posCustomerRepository;
    private $partner;

    public function __construct(SmanagerUserServerClient $smanagerUserServerClient, PosOrderServerClient $posOrderServerClient, PosCustomerRepository $posCustomerRepository)
    {
        $this->smanagerUserServerClient = $smanagerUserServerClient;
        $this->posOrderServerClient = $posOrderServerClient;
        $this->posCustomerRepository = $posCustomerRepository;
    }

    /**
     * @param Partner $partner
     * @return SmanagerUserService
     */
    public function setPartner( Partner $partner)
    {
        $this->partner = $partner;
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
    public function getDetails(): array
    {
        $customer_info = $this->getCustomerInfoFromSmanagerUserService();
        list($total_purchase_amount,$total_used_promo) = $this->getPurchaseAmountAndTotalUsedPromo();
        list($total_due_amount,$total_payable_amount) = $this->getDueAndPayableAmount();

        $customer_details = [];
        $customer_details['id'] = $customer_info['_id'] ?? null;
        $customer_details['name'] = $customer_info['name'] ?? null;
        $customer_details['phone'] = $customer_info['phone'] ?? null;
        $customer_details['email'] = $customer_info['email'] ?? null;
        $customer_details['address'] = $customer_info['address'] ?? null;
        $customer_details['image'] = $customer_info['pro_pic'] ?? null;
        $customer_details['customer_since'] = $customer_info['created_at'] ?? null;
        $customer_details['customer_since_formatted'] = isset($customer_info['created_at']) ? Carbon::parse($customer_info['created_at'])->diffForHumans(): null;
        $customer_details['total_purchase_amount'] = $total_purchase_amount;
        $customer_details['total_used_promo'] = $total_used_promo;
        $customer_details['total_due_amount'] = $total_due_amount;
        $customer_details['total_payable_amount'] = $total_payable_amount;
        $customer_details['is_customer_editable'] = true;
        $customer_details['note'] = $customer_info['note'] ?? null;
        $customer_details['is_supplier'] = $customer_info['is_supplier'] ?? 0;

        return $customer_details;
    }
    public function showCustomerListByPartnerId()
    {
        return $this->getCustomerListByPartnerId();

    }

    /**
     * @return mixed
     */
    private function getCustomerInfoFromSmanagerUserService()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/'.$this->partner->id.'/pos-users/'.$this->customerId);
    }

    /**
     * @return array
     */
    private function getPurchaseAmountAndTotalUsedPromo(): array
    {
        $response = $this->posOrderServerClient->get('api/v1/customers/'.$this->customerId.'/order-amount');
        return [$response['total_purchase_amount'],$response['total_used_promo']];
    }

    /**
     * @throws InvalidPartnerPosCustomer
     * @throws AccountingEntryServerError
     */
    private function getDueAndPayableAmount(): array
    {
        $customer_amount =  $this->posCustomerRepository->getDueAmountFromDueTracker($this->partner, $this->customerId);
        return [$customer_amount['due'],$customer_amount['payable']];
    }

    private function getCustomerListByPartnerId()
    {
        return $this->smanagerUserServerClient->get('api/v1/partners/'.$this->partner->id);
    }
}