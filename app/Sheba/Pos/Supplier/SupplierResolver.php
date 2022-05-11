<?php

namespace Sheba\Pos\Supplier;

use App\Models\Partner;
use App\Sheba\PosCustomerService\SmanagerUserServerClient;
use Exception;

class SupplierResolver
{
    /** @var Partner $partner */
    private $partner;
    /** @var string $supplierId */
    private $supplierId;
    /** @var SupplierObject $supplierObject */
    private $supplierObject;
    /** @var SmanagerUserServerClient */
    private $smanagerUserServerClient;

    public function __construct(SmanagerUserServerClient $smanagerUserServerClient, SupplierObject $supplierObject)
    {
        $this->smanagerUserServerClient = $smanagerUserServerClient;
        $this->supplierObject = $supplierObject;
    }

    /**
     * @param Partner $partner
     * @return SupplierResolver
     */
    public function setPartner(Partner $partner): SupplierResolver
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param string $supplierId
     * @return SupplierResolver
     */
    public function setSupplierId(string $supplierId): SupplierResolver
    {
        $this->supplierId = $supplierId;
        return $this;
    }

    public function get()
    {
        try {
            $supplier = $this->smanagerUserServerClient->get('api/v1/partners/' . $this->partner->id . '/suppliers/' . $this->supplierId);
            return $this->supplierObject->setId($supplier['_id'])->setPartnerId($supplier['partner_id'])->setName($supplier['name'])
                ->setMobile($supplier['mobile'])->setEmail($supplier['email'])->setGender($supplier['gender'])
                ->setDob($supplier['dob'])->setProPic($supplier['pro_pic'])->setCompanyName($supplier['company_name'])
                ->setAddress($supplier['address']);
        } catch (Exception $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }

}