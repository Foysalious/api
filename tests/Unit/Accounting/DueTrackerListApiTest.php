<?php

namespace Tests\Unit\Accounting;

/**
 * @author Zubayer alam <zubayer@sheba.xyz>
 */
class DueTrackerListApiTest extends AccountingFeatureTest
{
    public function test_due_tracker_list()
    {
        $response = $this->get(config('sheba.api_url').'/v2/accounting/due-tracker/due-list', [
                'Authorization' => $this->token ?? $this->generateToken(),
            ]);
        $response->assertResponseOk();
        $response->seeJsonStructure([
                'code',
                'message',
                'data' => [
                    'list',
                    'total_transactions',
                    'total',
                    'stats',
                    'partner' => [
                        'name',
                        'avatar',
                        'mobile',
                    ],
                ],
            ]);
    }
}
