<?php namespace App\Sheba\WebstoreBanner\Listeners;

use App\Jobs\WebstoreSettingsSyncJob;
use App\Models\Partner;
use App\Sheba\UserMigration\Modules;
use App\Sheba\WebstoreBanner\Events\WebstoreBannerUpdate;
use App\Sheba\WebstoreSetting\WebstoreSettingService;


class WebstoreBannerListener
{
    public function __construct()
    {

    }

    public function handle(WebstoreBannerUpdate $event)
    {
        $partner = Partner::find($event->getPartnerId());
        if ($partner->isMigrated(Modules::POS)) {
            /** @var WebstoreSettingService $service */
            $service = app(WebstoreSettingService::class);
            $service->setPartner($event->getPartnerId())->sync();
        }
    }
}
