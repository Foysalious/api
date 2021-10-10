<?php namespace Sheba\Pos\Customer;


use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Sheba\PosOrderService\SmanagerUserServerClient;

class PosCustomerResolver
{
    private $customerId;
    /** @var Partner $partner */
    private $partner;
    /** @var SmanagerUserServerClient */
    private $smanagerUserServerClient;
    /** @var PosCustomerObject */
    private $posCustomerObject;

    public function __construct(SmanagerUserServerClient $smanagerUserServerClient, PosCustomerObject $posCustomerObject)
    {
        $this->smanagerUserServerClient = $smanagerUserServerClient;
        $this->posCustomerObject = $posCustomerObject;

    }

    /**
     * @param mixed $customerId
     * @return PosCustomerResolver
     */
    public function setCustomerId($customerId): PosCustomerResolver
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return PosCustomerResolver
     */
    public function setPartner(Partner $partner): PosCustomerResolver
    {
        $this->partner = $partner;
        return $this;
    }

    public function get(): PosCustomerObject
    {
        if ($this->partner->isMigrationCompleted()) {
            $customer = $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partner->id . '/users/' . $this->customerId);
            return $this->posCustomerObject->setId($customer['_id'])->setPartnerId($customer['partner_id'])->setName($customer['name'])
                ->setIsSupplier($customer['is_supplier'])->setMobile($customer['mobile'])->setEmail($customer['email'])
                ->setGender($customer['gender'])->setDob($customer['dob'])->setProPic($customer['_id']);

        }
        $partner_pos_customer = PartnerPosCustomer::where('customer_id', $this->customerId)->where('partner_id', $this->partner->id)
            ->with(['customer' => function($q) {
                $q->with('profile');
            }])->first();
        return $this->posCustomerObject->setId($partner_pos_customer->customer_id)->setPartnerId($partner_pos_customer->partner_id)
            ->setName($partner_pos_customer->nick_name ?: $partner_pos_customer->customer->profile->name)->setIsSupplier($partner_pos_customer->is_supplier)
            ->setMobile($partner_pos_customer->customer->profile->name)->setEmail($partner_pos_customer->customer->profile->email)
            ->setGender($partner_pos_customer->customer->profile->gender)->setDob($partner_pos_customer->customer->profile->dob)
            ->setProPic($partner_pos_customer->customer->profile->pro_pic);

    }
}