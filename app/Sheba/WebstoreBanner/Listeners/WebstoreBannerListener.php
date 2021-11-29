<?php namespace App\Sheba\WebstoreBanner\Listeners;

use App\Jobs\WebstoreSettingsSyncJob;
use App\Sheba\WebstoreBanner\Events\WebstoreBannerUpdate;


class WebstoreBannerListener
{
    public function __construct()
    {

    }

    public function handle(WebstoreBannerUpdate $event)
    {
        dispatch(new WebStoreSettingsSyncJob($event->getPartnerId()));
    }
}
