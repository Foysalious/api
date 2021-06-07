<?php namespace Tests\Feature\Accounting;


use Illuminate\Support\Facades\Log;

class IncomeExpenseAccountListWithTotalDebitTest extends AccountingFeatureTest
{
    private $start_date = "2021-01-29";
    private $end_date = "2021-12-29";

    public function test_income_accounts_list()
    {
        $response = $this->get(config('sheba.api_url')."/v2/accounting/income-expense-total?account_type=income&start_date=$this->start_date&end_date=$this->end_date", [
            'Authorization' => $this->token ?? $this->generateToken()
        ]);
        $response->assertResponseOk();
        $response->seeJsonStructure(["code", "message", "data"]);
    }

    public function test_expense_accounts_list()
    {
        $response = $this->get(config('sheba.api_url')."/v2/accounting/income-expense-total?account_type=expense&start_date=$this->start_date&end_date=$this->end_date", [
            'Authorization' => $this->token ?? $this->generateToken()
        ]);
        $response->assertResponseOk();
        $response->seeJsonStructure(["code", "message", "data"]);
    }

}