<?php

namespace Tests\Feature\Digigo\Support;

use Sheba\Dal\Support\Model as Support;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class TicketListGetApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Support::class]);
        $this->logIn();
        Support::factory()->create();
    }

    public function testApiReturnSupportListAccordingToLimitParams()
    {
        $response = $this->get("/v1/employee/supports?status=open&limit=5&offset=0", [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
    }
}
