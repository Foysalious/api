<?php namespace App\Sheba\InventoryService\Services\SyncService;

use App\Sheba\InventoryService\InventoryServerClient;

class PartnerInventorySetting extends Service
{
    protected $uri;

    public function __construct(InventoryServerClient $client)
    {
        parent::__construct($client);
    }

    public function syncSettings()
    {
       $data = $this->makeData();
       $this->makeUri();
       $this->client->put($this->uri, $data, false);
    }

    private function makeUri()
    {
        $this->uri = 'api/v1/partners/' . $this->partner->id;
    }

    private function makeData()
    {
        return [
            'vat_percentage' => $this->partner->posSetting->vat_percentage ?? 0,
            'sub_domain' => $this->partner->sub_domain,
        ];
    }

}