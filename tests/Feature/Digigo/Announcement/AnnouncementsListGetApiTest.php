<?php

namespace Tests\Feature\Digigo\Announcement;

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
    }
}
