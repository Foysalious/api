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
        $data = [
            'name' => $this->partner->name,
            'sub_domain' => $this->partner->sub_domain,
        ];
        $pos_settings = $this->partner->posSetting;
        if ($pos_settings) {
            $pos_data['sms_invoice'] = $pos_settings->sms_invoice;
            $pos_data['auto_printing'] = $pos_settings->auto_printing;
            if(!is_null($pos_settings->printer_name)) $pos_data['printer_name'] = $pos_settings->printer_name;
            if(!is_null($pos_settings->printer_model)) $pos_data['printer_model'] = $pos_settings->printer_model;
            $data = array_merge($data,$pos_data);
        }
        return $data;
    }

    private function makeUri()
    {
        $this->uri = 'api/v1/partners/' . $this->partner->id;
    }

}