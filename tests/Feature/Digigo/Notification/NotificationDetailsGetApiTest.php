<?php

namespace Tests\Feature\Digigo\Notification;

use Carbon\Carbon;
use Database\Factories\AnnouncementFactory;
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
        $this->truncateTables([Announcement::class, BusinessPushNotificationLog::class]);
        $this->logIn();
        Announcement::factory()->create();
        BusinessPushNotificationLog::factory()->create();
    }

    public function testApiReturnNotificationDetailsAccordingToNotificationId()
    {
        $response = $this->get("/v1/employee/announcements/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getNotificationDetailsFromDatabase($data);
        $this->returnNotificationDetailsInArrayFormat($data);
    }

    private function getNotificationDetailsFromDatabase($data)
    {
        /**
         *  User Emergency Data @return AnnouncementFactory
         */
        $this->assertEquals(1, $data['announcement']['id']);
        $this->assertEquals('Holiday notice', $data['announcement']['title']);
        $this->assertEquals('holiday', $data['announcement']['type']);
        $this->assertEquals('As you know the current situation is a work situation. You can work the hole day and you should as you have no interruption', $data['announcement']['short_description']);
        $this->assertEquals('As you know the current situation is a work situation. You can work the hole day and you should as you have no interruption', $data['announcement']['description']);
        $this->assertEquals('Previous', $data['announcement']['status']);
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($data['announcement']['end_date'])->format('Y-m-d H:i'));
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($data['announcement']['created_at'])->format('Y-m-d H:i'));
    }

    private function returnNotificationDetailsInArrayFormat($data)
    {
        $this->assertArrayHasKey('id', $data['announcement']);
        $this->assertArrayHasKey('title', $data['announcement']);
        $this->assertArrayHasKey('type', $data['announcement']);
        $this->assertArrayHasKey('short_description', $data['announcement']);
        $this->assertArrayHasKey('description', $data['announcement']);
        $this->assertArrayHasKey('status', $data['announcement']);
        $this->assertArrayHasKey('end_date', $data['announcement']);
        $this->assertArrayHasKey('created_at', $data['announcement']);
    }
}
