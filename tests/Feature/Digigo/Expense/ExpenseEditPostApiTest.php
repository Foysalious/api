<?php

namespace Tests\Feature\Digigo\Expense;

use Sheba\Dal\Expense\Expense;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class ExpenseEditPostApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Expense::class]);
        $this->logIn();
        Expense::factory()->create([
            'member_id' => $this->member->id,
            'business_member_id' => $this->business_member->id,
            'amount' => '100',
            'remarks' => 'Test Expense',
            'type' => 'other',
        ]);
    }

    public function testCheckAPiReturnSuccessResponseAndUpdateExpenseDetailsWithValidExpenseFieldInfo()
    {
        $response = $this->post("/v1/employee/expense/1", [
            'amount' => '200',
            'remarks' => 'Test Expense',
            'type' => 'other',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
    }
}