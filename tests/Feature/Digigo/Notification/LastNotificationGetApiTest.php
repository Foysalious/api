<?php

namespace Tests\Feature\Digigo\Notification;

use App\Models\Notification;
use Carbon\Carbon;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\BusinessPushNotificationLogs\Model as BusinessPushNotificationLog;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class LastNotificationGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Notification::class, BusinessPushNotificationLog::class]);
        $this->logIn();
        Notification::factory()->create();
        BusinessPushNotificationLog::factory()->create();
    }

    public function testApiReturnUnreadNotificationCount()
    {
        $response = $this->get("/v1/employee/last-notifications?time=".Carbon::now()->format('Y-m-d H:i:s')."", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/last-notifications?time=".Carbon::now()->format('Y-m-d H:i:s')."", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(1, $data['notifications']);
    }

    public function testLastNotificationDataApiFormat()
    {
        $response = $this->get("/v1/employee/last-notifications?time=".Carbon::now()->format('Y-m-d H:i:s')."", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertArrayHasKey('notifications', $data);
    }
}
