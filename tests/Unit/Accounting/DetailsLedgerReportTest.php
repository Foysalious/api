<?php


namespace Tests\Feature\Accounting;


class DetailsLedgerReportTest extends AccountingFeatureTest
{
    public function test_report_response()
    {
        $response = $this->get(
            config(
                'sheba.api_url'
            ) . '/v2/accounting/reports/details_ledger_report?start_date=2021-05-10&end_date=2021-07-15&account_id=3',
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
                    'opening_balance',
                    'closing_balance',
                    'journal_list'
                ]
            ]
        );
    }
}