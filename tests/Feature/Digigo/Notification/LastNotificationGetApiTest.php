<?php

namespace Tests\Feature\Digigo\Notification;

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
        $this->truncateTables([BusinessPushNotificationLog::class]);
        $this->logIn();
        BusinessPushNotificationLog::factory()->create();
    }

    public function testCheckAPiReturnUnreadNotificationCountAccordingToTimeParams()
    {
        $response = $this->get("/v1/employee/last-notifications?time=2021-11-28%2017:55:49", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}
