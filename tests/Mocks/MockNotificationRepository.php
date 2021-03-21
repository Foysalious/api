<?php


namespace Tests\Mocks;


use App\Models\Partner;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use App\Sheba\Subscription\Partner\PartnerSubscriptionChange;

class MockNotificationRepository extends NotificationRepository
{
    public function sendInsufficientNotification(Partner $partner, $package, $package_type, $grade, $withMessage = true)
    {

    }
}