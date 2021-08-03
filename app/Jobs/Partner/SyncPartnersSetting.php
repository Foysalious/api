<?php namespace App\Jobs\Partner;

use App\Jobs\Job;
use App\Sheba\InventoryService\Services\SyncService\PartnerInventorySetting;
use App\Sheba\PosOrderService\Services\SyncService\PartnerPosSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SyncPartnersSetting extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $model;
    /** @var PartnerPosSetting|PartnerInventorySetting $syncService */
    protected $syncServiceClass;

    public function __construct($model)
    {
        $this->model = $model;
        $this->queue = 'partner_sync';
    }

    /**
     * @param $syncServiceClass
     * @return SyncPartnersSetting
     */
    public function setSyncService($syncServiceClass)
    {
        $this->syncServiceClass = $syncServiceClass;
        return $this;
    }

    public function handle()
    {
        try {
            /** @var PartnerInventorySetting|PartnerPosSetting $sync_service */
            $sync_service = App::make($this->syncServiceClass);
            $sync_service->setModel($this->model)->syncSettings();
        } catch (\Exception $e) {
            logError($e->getMessage());
        }
    }
}