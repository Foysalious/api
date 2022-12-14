<?php

namespace App\Sheba\AccountingEntry\Repository;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Dto\EntryDTO;
use App\Sheba\Pos\Order\PosOrderObject;
use Carbon\Carbon;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\Pos\Order\PosOrderResolver;
use Exception;

class EntriesRepository extends BaseRepository
{
    private $entry_id;
    private $partner;

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
            $data["customer_details"] = null;
            if ($data["contact_id"]) {
                /** @var PosCustomerResolver $posCustomerResolver */
                $posCustomerResolver = app(PosCustomerResolver::class);
                $posCustomer = $posCustomerResolver->setCustomerId($data["contact_id"])->setPartner($this->partner)->get();
                if ($posCustomer) {
                    $data["customer_details"] = [
                        'id' => $posCustomer->id,
                        'name' => $posCustomer->name,
                        'phone' => $posCustomer->mobile,
                        'image' => $posCustomer->pro_pic,
                        'is_supplier' => $posCustomer->is_supplier
                    ];
                }
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

    public function createEntry(array $data)
    {
        if (!$this->isMigratedToAccounting($this->partner->id)) {
            return true;
        }
        $this->setModifier($this->partner);
        $url = "api/entries/";
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->post($url, $data);
    }

    /**
     * @param int $orderId
     * @return PosOrderObject|null
     */
    private function posOrderByOrderId(int $orderId)
    {
        try {
            /** @var PosOrderResolver $posOrderResolver */
            $posOrderResolver = app(PosOrderResolver::class);
            return $posOrderResolver->setOrderId($orderId)->get();
        } catch (Exception $e) {
            return null;
        }
    }

    public function updateEntry(array $data, int $entry_id)
    {
        $url = "api/entries/" . $entry_id;
        return $this->client->setUserType(UserType::PARTNER)->setUserId($this->partner->id)->post($url, $data);
    }


}