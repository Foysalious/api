<?php namespace App\Providers;

use App\Jobs\WebstoreSettingsSyncJob;
use App\Sheba\InventoryService\Partner\Events\Updated as PartnerUpdatedEvent;
use App\Sheba\InventoryService\Partner\Listeners\Updated as PartnerUpdatedListener;

use App\Sheba\PosOrderService\PosSetting\Events\Created as PosSettingCreatedEvent;
use App\Sheba\PosOrderService\PosSetting\Listeners\Created as PosSettingCreatedListener;
use App\Sheba\PosOrderService\PosSetting\Events\Updated as PosSettingUpdatedEvent;
use App\Sheba\PosOrderService\PosSetting\Listeners\Updated as PosSettingUpdatedListener;

use App\Sheba\WebstoreBanner\Events\WebstoreBannerUpdate;
use App\Sheba\WebstoreBanner\Listeners\WebstoreBannerListener;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Sheba\Business\BusinessMember\Events\BusinessMemberCreated;
use Sheba\Business\BusinessMember\Events\BusinessMemberDeleted;
use Sheba\Business\BusinessMember\Events\BusinessMemberUpdated;
use Sheba\Business\BusinessMember\Listeners\BusinessMemberCreatedListener;
use Sheba\Business\BusinessMember\Listeners\BusinessMemberUpdatedListener;
use Sheba\Business\BusinessMember\Listeners\BusinessMemberDeletedListener;
use Sheba\Dal\PartnerPosService\Events\PartnerPosServiceSaved;
use Sheba\Pos\Product\Listeners\WebstorePublishCheck;
use Sheba\TopUp\Events\TopUpRequestOfBlockedNumber as TopUpRequestOfBlockedNumberEvent;
use Sheba\TopUp\Listeners\TopUpRequestOfBlockedNumber;
use Sheba\Dal\Profile\Events\ProfilePasswordUpdated;
use Sheba\Profile\Listeners\ProfilePasswordUpdatedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        TopUpRequestOfBlockedNumberEvent::class => [
            TopUpRequestOfBlockedNumber::class
        ],
        WebstoreBannerUpdate::class => [
            WebstoreBannerListener::class
        ],
        ProfilePasswordUpdated::class => [
            ProfilePasswordUpdatedListener::class
        ],
        BusinessMemberCreated::class => [
            BusinessMemberCreatedListener::class
        ],
        BusinessMemberUpdated::class => [
            BusinessMemberUpdatedListener::class
        ],
        BusinessMemberDeleted::class => [
            BusinessMemberDeletedListener::class
        ],
        PartnerUpdatedEvent::class => [
            PartnerUpdatedListener::class,
        ],
        PosSettingCreatedEvent::class => [
            PosSettingCreatedListener::class
        ],
        PosSettingUpdatedEvent::class => [
            PosSettingUpdatedListener::class
        ]
    ];
}
