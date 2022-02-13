<?php

namespace Tests\Feature\Digigo\Expense;

use Database\Factories\ExpensesFactory;
use Sheba\Dal\Expense\Expense;
use Tests\Feature\FeatureTestCase;

/**
 * @author Khairun Nahar <khairun@sheba.xyz>
 */
class DeleteExpenseDeleteApiTest extends FeatureTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->truncateTables([Expense::class]);
        $this->logIn();
        Expense::factory()->create([
            'member_id' => $this->member->id,
            'business_member_id' => $this->business_member->id,
        ]);
    }

    public function testApiReturnSuccessResponseAfterDeleteExpenseFromExpenseDatabase()
    {
        $response = $this->delete("/v1/employee/expense/1", [], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->json();
        $expense = Expense::first();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Successful', $data['message']);
        /**
         *   expense Data delete @return ExpensesFactory
         */
        $this->assertEquals(null, $expense);
    }
}
