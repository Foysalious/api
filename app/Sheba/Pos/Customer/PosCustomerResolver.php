<?php namespace Sheba\Pos\Customer;


use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Sheba\PosCustomerService\SmanagerUserServerClient;
use App\Sheba\UserMigration\Modules;
use Exception;

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

    /**
     * @return PosCustomerObject|null
     * @throws Exception
     */
    public function get()
    {
        if ($this->partner->isMigrated(Modules::POS)) {
            try {
                $customer = $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partner->id . '/users/' . $this->customerId);
                return $this->posCustomerObject->setId($customer['_id'])->setPartnerId($customer['partner_id'])->setName($customer['name'])
                    ->setIsSupplier($customer['is_supplier'])->setMobile($customer['mobile'])->setEmail($customer['email'])
                    ->setGender($customer['gender'])->setDob($customer['dob'])->setProPic($customer['pro_pic']);
            } catch (Exception $e) {
                app('sentry')->captureException($e);
                return null;
            }
        }
        $partner_pos_customer = PartnerPosCustomer::where('customer_id', $this->customerId)->where('partner_id', $this->partner->id)
            ->with(['customer' => function($q) {
                $q->with('profile');
            }])->first();
        if (!$partner_pos_customer) return null;
        return $this->posCustomerObject->setId($partner_pos_customer->customer_id)->setPartnerId($partner_pos_customer->partner_id)
            ->setName($partner_pos_customer->nick_name ?: $partner_pos_customer->customer->profile->name)->setIsSupplier($partner_pos_customer->is_supplier)
            ->setMobile($partner_pos_customer->customer->profile->mobile)->setEmail($partner_pos_customer->customer->profile->email)
            ->setGender($partner_pos_customer->customer->profile->gender)->setDob($partner_pos_customer->customer->profile->dob)
            ->setProPic($partner_pos_customer->customer->profile->pro_pic);

    }
}
