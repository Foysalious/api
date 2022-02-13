<?php

namespace Tests\Feature\Digigo\Notification;

use App\Models\Notification;
use Sheba\Dal\BusinessPushNotificationLogs\Model as BusinessPushNotificationLog;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class NotificationSeenPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Notification::class, BusinessPushNotificationLog::class]);
        $this->logIn();
        BusinessPushNotificationLog::factory()->create();
        Notification::factory()->create();
    }

    public function testApiReturnSuccessResponseWhenUserSeenAnUnseenNotificationFromList()
    {
        $response = $this->post("/v1/employee/notifications/seen", [
            'notifications' => '[1]',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}
