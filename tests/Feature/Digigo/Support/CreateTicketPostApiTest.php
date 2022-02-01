<?php

namespace Tests\Feature\Digigo\Support;

use Sheba\Dal\Support\Model as Support;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class CreateTicketPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Support::class]);
        $this->logIn();
        Support::factory()->create();
    }

    public function testApiReturnSuccessResponseAfterCreateNewSupport()
    {
        $response = $this->post("/v1/employee/supports", [
            'description' => 'Test Support ticket',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}
