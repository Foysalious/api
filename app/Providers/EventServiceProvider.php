<?php namespace App\Providers;


use App\Sheba\InventoryService\Partner\Events\Updated as PartnerUpdatedEvent;
use App\Sheba\InventoryService\Partner\Listeners\Updated as PartnerUpdatedListener;


use App\Sheba\Customer\Events\PartnerPosCustomerCreatedEvent;
use App\Sheba\Customer\Events\PartnerPosCustomerUpdatedEvent;
use App\Sheba\Customer\Jobs\AccountingCustomer\AccountingCustomerUpdateJob;
use App\Sheba\Customer\Listeners\PartnerPosCustomerCreateListener;
use App\Sheba\Customer\Listeners\PartnerPosCustomerUpdateListener;
use App\Sheba\PosOrderService\PosSetting\Events\Created as PosSettingCreatedEvent;
use App\Sheba\PosOrderService\PosSetting\Listeners\Created as PosSettingCreatedListener;
use App\Sheba\PosOrderService\PosSetting\Events\Updated as PosSettingUpdatedEvent;
use App\Sheba\PosOrderService\PosSetting\Listeners\Updated as PosSettingUpdatedListener;

use App\Sheba\UserMigration\Events\StatusUpdated as UserMigrationStatusUpdatedByHookEvent;
use App\Sheba\UserMigration\Listeners\StatusUpdatedListener as UserMigrationStatusUpdatedByHookListener;
use App\Sheba\WebstoreBanner\Events\WebstoreBannerUpdate;
use App\Sheba\WebstoreBanner\Listeners\WebstoreBannerListener;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psy\Util\Json;
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
use Sheba\UserAgentInformation;
use Sheba\Helpers\Logger\ApiLogger;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        PartnerPosCustomerCreatedEvent::class        => [
            PartnerPosCustomerCreateListener::class
        ],
        PartnerPosCustomerUpdatedEvent::class        => [
            PartnerPosCustomerUpdateListener::class
        ],
        TopUpRequestOfBlockedNumberEvent::class      => [
            TopUpRequestOfBlockedNumber::class
        ],
        WebstoreBannerUpdate::class                  => [
            WebstoreBannerListener::class
        ],
        ProfilePasswordUpdated::class                => [
            ProfilePasswordUpdatedListener::class
        ],
        BusinessMemberCreated::class                 => [
            BusinessMemberCreatedListener::class
        ],
        BusinessMemberUpdated::class                 => [
            BusinessMemberUpdatedListener::class
        ],
        BusinessMemberDeleted::class                 => [
            BusinessMemberDeletedListener::class
        ],
        PartnerUpdatedEvent::class                   => [
            PartnerUpdatedListener::class,
        ],
        PosSettingCreatedEvent::class                => [
            PosSettingCreatedListener::class
        ],
        PosSettingUpdatedEvent::class                => [
            PosSettingUpdatedListener::class
        ],
        UserMigrationStatusUpdatedByHookEvent::class => [
            UserMigrationStatusUpdatedByHookListener::class
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    /* public function boot()
    {
        Event::listen("kernel.handled", function ($request, $response) {
            try {
                (new ApiLogger($request, $response))->log();
            } catch (\Throwable $e) {
                \Log::error($e->getMessage());
            }
        });
    }*/
}
