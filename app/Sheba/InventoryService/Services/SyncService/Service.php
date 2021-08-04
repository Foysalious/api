<?php namespace App\Sheba\InventoryService\Services\SyncService;


use App\Models\Partner;

class Service
{
    protected $client;
    protected $model;
    protected $modelName;
    public $partner;
    const PARTNER_POS_SETTING = 'App\Models\PartnerPosSetting';
    const PARTNER = 'App\Models\Partner';

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
        $this->setModelName(get_class($this->model));
        $this->setPartner();

        return $this;
    }

    /**
     * @param mixed $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }


    public function setPartner()
    {
        if($this->modelName == self::PARTNER) {
            $this->partner = $this->model;
        } else {
            $this->partner = Partner::where('id', $this->model->partner_id)->first();
        }

    }
}