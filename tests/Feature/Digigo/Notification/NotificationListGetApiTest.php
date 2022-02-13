<?php

namespace Tests\Feature\Digigo\Notification;

use App\Models\Notification;
use Carbon\Carbon;
use Database\Factories\AnnouncementFactory;
use Sheba\Dal\Announcement\Announcement;
use Sheba\Dal\BusinessPushNotificationLogs\Model as BusinessPushNotificationLog;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class NotificationListGetApiTest extends FeatureTestCase
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

    public function testApiReturnNotificationListAccordingToLimitParams()
    {
        $response = $this->get("/v1/employee/notifications?limit=1&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getNotificationListFromDatabase($data);
        $this->returnNotificationListDataInArrayFormat($data);
    }

    private function getNotificationListFromDatabase($data)
    {
        /**
         *  User Emergency Data @return AnnouncementFactory
         */
        foreach ($data['notifications'] as $item) {
            $this->assertEquals(1, $item['id']);
            $this->assertEquals('Test notification', $item['message']);
            $this->assertEquals('announcement', $item['type']);
            $this->assertEquals(1, $item['type_id']);
            $this->assertEquals(0, $item['is_seen']);
            $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($item['created_at'])->format('Y-m-d H:i'));
        }
    }

    private function returnNotificationListDataInArrayFormat($data)
    {
        foreach ($data['notifications'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('message', $item);
            $this->assertArrayHasKey('type', $item);
            $this->assertArrayHasKey('type_id', $item);
            $this->assertArrayHasKey('is_seen', $item);
            $this->assertArrayHasKey('created_at', $item);
        }
    }
}
