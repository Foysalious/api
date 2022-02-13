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

    public function testCreateNewExpenseAndStoreInExpenseTable()
    {
        $response = $this->post("/v1/employee/expense", [
            'amount' => '100',
            'remarks' => 'Test Expense',
            'type' => 'transport',
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $expense = Expense::first();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        $this->assertEquals(1, $data['expense']['id']);
        $this->assertArrayHasKey('id', $data['expense']);
        $this->assertEquals($this->member->id, $expense->member_id);
        $this->assertEquals($this->business_member->id, $expense->business_member_id);
        $this->assertEquals('100.00', $expense->amount);
        $this->assertEquals('Test Expense', $expense->remarks);
        $this->assertEquals('transport', $expense->type);
        $this->assertEquals('pending', $expense->status);
    }
}
