<?php

namespace App\Providers;

use App\Sheba\InventoryService\Events\PartnerModelUpdated;
use App\Sheba\InventoryService\Events\PartnerPosSettingUpdated;
use App\Sheba\InventoryService\Listeners\PartnerModelUpdatedListener;
use App\Sheba\InventoryService\Listeners\PartnerPosSettingUpdatedListener;
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
//        PartnerPosSettingUpdated::class => [
//                PartnerPosSettingUpdatedListener::class,
//            ],
//        PartnerModelUpdated::class => [
//            PartnerModelUpdatedListener::class,
//        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param DispatcherContract $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
