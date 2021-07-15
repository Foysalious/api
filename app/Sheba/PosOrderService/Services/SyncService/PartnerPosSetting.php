<?php namespace App\Sheba\PosOrderService\Services\SyncService;

use App\Sheba\InventoryService\Services\SyncService\Service;
use App\Sheba\PosOrderService\PosOrderServerClient;

class PartnerPosSetting extends Service
{
    protected $uri;
    const PARTNER_POS_SETTING = 'App\Models\PartnerPosSetting';
    const PARTNER = 'App\Models\Partner';

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
        $data = [];

        if(self::PARTNER_POS_SETTING == $this->modelName){
            $data = [
                'sms_invoice' => $this->model->sms_invoice,
                'auto_printing' => $this->model->auto_printing,
                'printer_name' => $this->model->printer_name,
                'printer_model' => $this->model->printer_model,
            ];
        }

        if(self::PARTNER == $this->modelName){
            $data = [
                'name' => $this->model->name,
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