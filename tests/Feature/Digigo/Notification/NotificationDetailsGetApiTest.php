<?php

namespace Tests\Feature\Digigo\Notification;

use App\Models\Notification;
use Sheba\Dal\Announcement\Announcement;
use Sheba\Dal\BusinessPushNotificationLogs\Model as BusinessPushNotificationLog;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class NotificationDetailsGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Announcement::class, Notification::class, BusinessPushNotificationLog::class]);
        $this->logIn();
        Announcement::factory()->create();
        BusinessPushNotificationLog::factory()->create();
        Notification::factory()->create();
    }

    public function testApiReturnNotificationDetailsAccordingToNotificationID()
    {
        $response = $this->get("/v1/employee/announcements/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}
