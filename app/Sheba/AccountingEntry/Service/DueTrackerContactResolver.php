<?php

namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Constants\ContactType;
use Sheba\Pos\Customer\PosCustomerResolver;

class DueTrackerContactResolver
{
    public $contactId;
    public $contactType;
    public $partner;

    /**
     * @param mixed $contactId
     */
    public function setContactId($contactId): DueTrackerContactResolver
    {
        $this->contactId = $contactId;
        return $this;
    }

    /**
     * @param mixed $contactType
     */
    public function setContactType($contactType): DueTrackerContactResolver
    {
        $this->contactType = $contactType;
        return $this;
    }

    /**
     * @param mixed $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }



    public function getContactDetails(): array
    {
        if ($this->contactType == ContactType::CUSTOMER) {
            $posCustomerResolver = app(PosCustomerResolver::class);
            $contact_detail = $posCustomerResolver->setCustomerId($this->contactId)->setPartner($this->partner)->get();
        } else {

        }
        return [
            'contact_id' => $contact_detail->id,
            'contact_name' => $contact_detail->name,
            'contact_mobile' => $contact_detail->mobile,
            'contact_pro_pic' => $contact_detail->pro_pic,
        ];
    }


}