<?php namespace App\Sheba\InventoryService\Services;

use App\Sheba\InventoryService\Exceptions\InventoryServiceServerError;
use App\Sheba\InventoryService\InventoryServerClient;

class PartnerService
{
    /**
     * @var InventoryServerClient
     */
    private $client;
    private  $partner;
    private $vatPercentage;
    /**
     * @var string
     */
    private $uri;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }


    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $vatPercentage
     * @return PartnerService
     */
    public function setVatPercentage($vatPercentage)
    {
        $this->vatPercentage = $vatPercentage;
        return $this;
    }

    /**
     * @throws InventoryServiceServerError
     */
    public function update()
    {
        $data = $this->makeUpdateData();
        $this->makeUri();
        $this->client->put($this->uri, $data, false);
    }

    public function get()
    {
        $this->makeUri();
        return $this->client->get($this->uri);
    }

    private function makeUri()
    {
        $this->uri = 'api/v1/partners/' . $this->partner->id;
    }

    private function makeUpdateData()
    {
        $data = [];
        if($this->vatPercentage) $data['vat_percentage'] = $this->vatPercentage;
        return $data;
    }




}