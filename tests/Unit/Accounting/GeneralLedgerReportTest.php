<?php

namespace Tests\Feature\Accounting;

class GeneralLedgerReportTest extends AccountingFeatureTest
{
    public function test_report_response()
    {
        $response = $this->get(
            config(
                'sheba.api_url'
            ) . '/v2/accounting/reports/general_ledger_report?start_date=2021-05-10&end_date=2021-07-15',
            [
                'Authorization' => $this->token ?? $this->generateToken()
            ]
        );
        $response->assertResponseOk();
        $response->seeJsonStructure(
            [
                'code',
                'message',
                'data'
            ]
        );
    }
}