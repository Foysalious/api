<?php

namespace App\Providers;

use App\Jobs\WebstoreSettingsSyncJob;
use App\Sheba\InventoryService\Partner\Events\Updated as PartnerUpdatedEvent;
use App\Sheba\InventoryService\Partner\Listeners\Updated as PartnerUpdatedListener;

use App\Sheba\PosOrderService\PosSetting\Events\Created as PosSettingCreatedEvent;
use App\Sheba\PosOrderService\PosSetting\Listeners\Created as PosSettingCreatedListener;
use App\Sheba\PosOrderService\PosSetting\Events\Updated as PosSettingUpdatedEvent;
use App\Sheba\PosOrderService\PosSetting\Listeners\Updated as PosSettingUpdatedListener;

use App\Sheba\UserMigration\Events\StatusUpdated as UserMigrationStatusUpdatedByHookEvent;
use App\Sheba\UserMigration\Listeners\StatusUpdatedListener as UserMigrationStatusUpdatedByHookListener;
use App\Sheba\WebstoreBanner\Events\WebstoreBannerUpdate;
use App\Sheba\WebstoreBanner\Listeners\WebstoreBannerListener;

use App\Sheba\Customer\Events\PartnerPosCustomerCreatedEvent;
use App\Sheba\Customer\Events\PartnerPosCustomerUpdatedEvent;
use App\Sheba\Customer\Jobs\AccountingCustomer\AccountingCustomerUpdateJob;
use App\Sheba\Customer\Listeners\PartnerPosCustomerCreateListener;
use App\Sheba\Customer\Listeners\PartnerPosCustomerUpdateListener;
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
use Sheba\TopUp\Events\TopUpRequestOfBlockedNumber as TopUpRequestOfBlockedNumberEvent;
use Sheba\TopUp\Listeners\TopUpRequestOfBlockedNumber;
use Sheba\Dal\Profile\Events\ProfilePasswordUpdated;
use Sheba\Profile\Listeners\ProfilePasswordUpdatedListener;
use Sheba\UserAgentInformation;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen
        = [
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
     * @param DispatcherContract $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
        $events->listen("kernel.handled", function (Request $request, Response $response) {
            $logPath = storage_path() . '/logs/api.log';
            try {
                $api_url       = $request->getUri();
                if ($api_url=="http://127.0.0.1/") return;
                $agent = new UserAgentInformation();
                $agent->setRequest($request);
                $ip            = $agent->getIp();
                $payload       = json_encode($request->all());
                $headers       = json_encode($request->header());
                $userAgent     = $agent->getUserAgent();
                $response_     = $response->getContent();
                $response_data = json_decode($response_, true);
                $status_code   = $response_data && array_key_exists('code', $response_data) ? $response_data['code'] : $response->getStatusCode();
                $len           = mb_strlen($response_, '8bit');
                if ($len > 10000) {
                    $response_ = mb_strcut($response_, 0, 10000);
                }
                $logger = new Logger("api_logger");
                $logger->pushHandler((new StreamHandler("$logPath"))->setFormatter(new JsonFormatter()), Logger::INFO);
                $logger->info("requestINFO", ['uri' => $api_url, "headers" => $headers, "status_code" => $status_code, "payload" => $payload, "agent" => $userAgent, "response" => $response_, "ip" => $ip, "app_version" => $agent->getApp(), "portal" => $agent->getPortalName()]);
            } catch (\Throwable $e) {
                \Log::error($e->getMessage());
            }
        });
        //
    }
}
