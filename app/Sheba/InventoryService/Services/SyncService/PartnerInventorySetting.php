<?php namespace App\Sheba\InventoryService\Services\SyncService;

use App\Sheba\InventoryService\InventoryServerClient;

class PartnerInventorySetting extends Service
{
    protected $uri;
    const PARTNER_POS_SETTING = 'App\Models\PartnerPosSetting';
    const PARTNER = 'App\Models\Partner';

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

    private function makeData(): array
    {
        $data = [];

        if(self::PARTNER_POS_SETTING == $this->modelName){
            $data = [
                'vat_percentage' => $this->model->vat_percentage
            ];
        }

        if(self::PARTNER == $this->modelName){
            $data = [
                 'sub_domain' => $this->model->sub_domain
            ];
        }
        return $data;
    }

    private function makeUri()
    {
        if($this->modelName == self::PARTNER) {
            $partner_id = $this->model->id;
        } else {
            $partner_id = $this->model->partner_id;
        }
        $this->uri = 'api/v1/partners/' . $partner_id;
    }

}