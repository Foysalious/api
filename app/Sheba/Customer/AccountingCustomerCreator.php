<?php namespace App\Sheba\Customer;

use Sheba\AccountingEntry\Repository\AccountingEntryClient;
use Sheba\ExpenseTracker\EntryType;

class AccountingCustomerCreator
{
    private $id;
    private $name;
    private $mobile;
    private $partner;

    private $client;
    private $picture;

    public function __construct(AccountingEntryClient $client)
    {
        $this->client = $client;
    }

    public function setPartnerId($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setCustomerId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setCustomerName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setCustomerProfilePicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    public function setCustomerMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    private function makeData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'pro_pic'=> $this->picture
        ];
    }

    public function storeAccountingCustomer()
    {
        $userType = EntryType::PARTNER;
        $userId = $this->partner;
        return $this->client->setUserType($userType)->setUserId($userId)->post('api/customers/', $this->makeData());
    }

}
