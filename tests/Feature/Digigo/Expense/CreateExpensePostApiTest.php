<?php

namespace Tests\Feature\Digigo\Expense;

use Sheba\Dal\Expense\Expense;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class CreateExpensePostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Expense::class]);
        $this->logIn();
    }

    public function testApiReturnSuccessResponseAfterCreateExpenseWithValidData()
    {
        $response = $this->post("/v1/employee/expense", [
            'amount' => '100',
            'remarks' => 'Test Expense',
            'type' => 'transport',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }

}
