<?php namespace App\Sheba\PosOrderService\Services\SyncService;

use App\Sheba\InventoryService\Services\SyncService\Service;
use App\Sheba\PosOrderService\PosOrderServerClient;

class PartnerPosSetting extends Service
{
    protected $uri;

    public function __construct(PosOrderServerClient $client)
    {
        parent::__construct($client);
    }

    public function syncSettings()
    {
       $data = $this->makeData();
       $this->makeUri();
       $this->client->put($this->uri, $data, false);
    }

    private function makeData(): array
    {
        $pos_settings = $this->partner->posSetting;
        $data = [
            'name' => $this->partner->name,
            'sub_domain' => $this->partner->sub_domain ?? 'default',
            'sms_invoice' => $pos_settings->sms_invoice ?? 0,
            'auto_printing' => $pos_settings->auto_printing ?? 0,
            'printer_name' => $pos_settings->printer_name ?? 'default',
            'printer_model' => $pos_settings->printer_model ?? 'NAI',
        ];
        return $data;
    }

    private function makeUri()
    {
        $this->uri = 'api/v1/partners/' . $this->partner->id;
    }

}