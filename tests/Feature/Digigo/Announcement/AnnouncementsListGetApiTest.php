<?php

namespace Tests\Feature\Digigo\Announcement;

use Carbon\Carbon;
use Database\Factories\AnnouncementFactory;
use Sheba\Dal\Announcement\Announcement;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class AnnouncementsListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Announcement::class]);
        $this->logIn();
        Announcement::factory()->create();
    }

    public function testApiReturnAnnouncementsListAccordingToLimitParams()
    {
        $response = $this->get("/v1/employee/announcements?limit=10&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->getAnnouncementListDataFromDatabase($data);
        $this->returnAnnouncementListDataInArrayFormat($data);
    }

    private function getAnnouncementListDataFromDatabase($data)
    {
        /**
         *  User Emergency Data @return AnnouncementFactory
         */
        $this->assertEquals(1, $data['announcements'][0]['id']);
        $this->assertEquals('Holiday notice', $data['announcements'][0]['title']);
        $this->assertEquals('holiday', $data['announcements'][0]['type']);
        $this->assertEquals('As you know the current situation is a work situation. You can work the hole day and you should as you have no interruption', $data['announcements'][0]['short_description']);
        $this->assertEquals('As you know the current situation is a work situation. You can work the hole day and you should as you have no interruption', $data['announcements'][0]['description']);
        $this->assertEquals('Previous', $data['announcements'][0]['status']);
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($data['announcements'][0]['end_date'])->format('Y-m-d H:i'));
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i'), Carbon::parse($data['announcements'][0]['created_at'])->format('Y-m-d H:i'));
    }

    private function returnAnnouncementListDataInArrayFormat($data)
    {
        $this->assertArrayHasKey('id', $data['announcements'][0]);
        $this->assertArrayHasKey('title', $data['announcements'][0]);
        $this->assertArrayHasKey('type', $data['announcements'][0]);
        $this->assertArrayHasKey('short_description', $data['announcements'][0]);
        $this->assertArrayHasKey('description', $data['announcements'][0]);
        $this->assertArrayHasKey('status', $data['announcements'][0]);
        $this->assertArrayHasKey('end_date', $data['announcements'][0]);
        $this->assertArrayHasKey('created_at', $data['announcements'][0]);
    }
}
