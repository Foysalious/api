<?php

namespace Tests\Feature\Digigo\Notification;

use App\Models\Notification;
use Carbon\Carbon;
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
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/notifications?limit=1&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        foreach ($data['notifications'] as $item) {
            $this->assertEquals(1,$item['id']);
            $this->assertEquals('Test notification',$item['message']);
            $this->assertEquals('announcement',$item['type']);
            $this->assertEquals(1,$item['type_id']);
            $this->assertEquals(0,$item['is_seen']);
            $this->assertEquals(Carbon::now(),$item['created_at']);
        }
    }

    public function testNotificationListDataApiFormat()
    {
        $response = $this->get("/v1/employee/notifications?limit=1&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        foreach ($data['notifications'] as $item) {
            $this->assertArrayHasKey('id',$item);
            $this->assertArrayHasKey('message',$item);
            $this->assertArrayHasKey('type',$item);
            $this->assertArrayHasKey('type_id',$item);
            $this->assertArrayHasKey('is_seen',$item);
            $this->assertArrayHasKey('created_at',$item);
        }
    }
}
