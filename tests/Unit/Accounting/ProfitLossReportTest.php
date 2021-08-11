<?php


namespace Tests\Feature\Accounting;


class ProfitLossReportTest extends AccountingFeatureTest
{
    public function test_report_response()
    {
        $response = $this->get(
            config(
                'sheba.api_url'
            ) . '/v2/accounting/reports/profit_loss_report?start_date=2021-05-10&end_date=2021-07-15',
            [
                'Authorization' => $this->token ?? $this->generateToken()
            ]
        );
        $response->assertResponseOk();
        $response->seeJsonStructure(
            [
                'code',
                'message',
                'data' => [
                    'operating_earning',
                    'cost_of_goods_sold',
                    'business_cost',
                    'non_operating_income',
                    'non_operating_expense',
                ]
            ]
        );
    }
}