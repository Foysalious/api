<?php

namespace App\Sheba\AccountingEntry\Repository;


use App\Models\PartnerPosCustomer;
use App\Sheba\AccountingEntry\Constants\UserType;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class EntriesRepository extends BaseRepository
{
    private $entry_id, $partner;

    /**
     * @param $entry_id
     * @return $this
     */
    public function setEntryId($entry_id)
    {
        $this->entry_id = $entry_id;
        return $this;
    }

    /**
     * @param $partner
     * @return $this
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function entryDetails()
    {
        try {
            $url = "api/entries/" . $this->entry_id;
            $data = $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->get($url);
            if ($data["attachments"]) {
                $data["attachments"] = json_decode($data["attachments"]);
            }
            if ($data["customer_id"]) {
                /** @var PartnerPosCustomer $partner_pos_customer */
                $partner_pos_customer = PartnerPosCustomer::where('partner_id', $this->partner->id)->where(
                    'customer_id',
                    $data["customer_id"]
                )->first();
                $customer_details = $partner_pos_customer->details();
                $data["customer_details"] = $customer_details;
            } else {
                $data["customer_details"] = null;
            }
            return $data;
        } catch (AccountingEntryServerError $e) {
            logError($e);
        }
    }

    /**
     * @return mixed
     */
    public function deleteEntry()
    {
        try {
            if (!$this->isMigratedToAccounting($this->partner->id)) {
                return true;
            }
            $url = "api/entries/" . $this->entry_id;
            return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->delete($url);
        } catch (AccountingEntryServerError $e) {
            logError($e);
        }
    }

}