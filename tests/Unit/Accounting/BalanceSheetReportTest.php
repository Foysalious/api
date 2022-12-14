<?php

namespace Tests\Unit\Accounting;

/**
 * @author Zubayer alam <zubayer@sheba.xyz>
 */
class BalanceSheetReportTest extends AccountingFeatureTest
{
    public function test_report_response()
    {
        $response = $this->get(config(
                'sheba.api_url'
            ).'/v2/accounting/reports/balance_sheet_report', [
                'Authorization' => $this->token ?? $this->generateToken(),
            ]);
        $response->assertResponseOk();
        $response->seeJsonStructure([
                'code',
                'message',
                'data' => [
                    "asset",
                    "liability",
                ],
            ]);
    }
}
