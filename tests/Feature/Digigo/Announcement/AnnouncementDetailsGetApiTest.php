<?php

namespace Tests\Feature\Digigo;

use Sheba\Dal\Announcement\Announcement;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */

class AnnouncementDetailsGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->truncateTables([Announcement::class]);
        $this->logIn();
        Announcement::factory()->create();
    }

    public function testCheckAPiReturnAnnouncementsDetailsAccordingToAnnouncementId()
    {
        $response = $this->get("/v1/employee/announcements/1", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
    }

}