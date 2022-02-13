<?php

namespace Tests\Feature\Digigo\Support;

use Database\Factories\SupportFactory;
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
    }

    public function testCreateNewSupportAndStoreInSupportTable()
    {
        $response = $this->post("/v1/employee/supports", [
            'description' => 'Test Support ticket',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $support = Support::first();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->assertEquals(1, $data['support']['id']);
        $this->assertArrayHasKey('id', $data['support']);
        /**
         *  User Support ticket Data @return SupportFactory
         */
        $this->assertEquals(1, $support->id);
        $this->assertEquals($this->member->id, $support->member_id);
        $this->assertEquals('Test Support ticket', $support->long_description);
        $this->assertEquals('open', $support->status);
    }
}
