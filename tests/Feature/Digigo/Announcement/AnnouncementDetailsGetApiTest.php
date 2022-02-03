<?php

namespace Tests\Feature\Digigo\Announcement;

use Carbon\Carbon;
use Sheba\Dal\Announcement\Announcement;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class AnnouncementDetailsGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Announcement::class]);
        $this->logIn();
        Announcement::factory()->create();
    }

    public function testApiReturnAnnouncementsDetails()
    {
        $response = $this->get("/v1/employee/announcements/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
      $this->assertEquals(200, $data['code']);
    }

    public function testApiReturnValidDataForSuccessResponse()
    {
        $response = $this->get("/v1/employee/announcements/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(1, $data['announcement']['id']);
        $this->assertEquals('Holiday notice', $data['announcement']['title']);
        $this->assertEquals('holiday', $data['announcement']['type']);
        $this->assertEquals('As you know the current situation is a work situation. You can work the hole day and you should as you have no interruption', $data['announcement']['short_description']);
        $this->assertEquals('As you know the current situation is a work situation. You can work the hole day and you should as you have no interruption', $data['announcement']['description']);
        $this->assertEquals('Previous', $data['announcement']['status']);
        $this->assertEquals(Carbon::now(), $data['announcement']['end_date']);
        $this->assertEquals(Carbon::now(), $data['announcement']['created_at']);
    }

    public function testAnnouncementDetailsDataApiFormat()
    {
        $response = $this->get("/v1/employee/announcements/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
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
