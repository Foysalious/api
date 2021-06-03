<?php


namespace Tests\Feature\Accounting;


use Tests\Feature\FeatureTestCase;

class DueTrackerListApiTest extends FeatureTestCase
{
    private $token = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoicmVhbCIsImltYWdlIjoiaHR0cHM6Ly9zMy5hcC1zb3V0aC0xLmFtYXpvbmF3cy5jb20vY2RuLXNoZWJhZGV2L2ltYWdlcy9wcm9maWxlcy9hdmF0YXIvMTU5OTYzMjMxM19yZWFsLmpwZWciLCJwcm9maWxlIjp7ImlkIjoyNTk5MzMsIm5hbWUiOiJyZWFsIiwiZW1haWxfdmVyaWZpZWQiOjB9LCJjdXN0b21lciI6eyJpZCI6MTg5ODk4fSwicmVzb3VyY2UiOnsiaWQiOjQ1MTc5LCJwYXJ0bmVyIjp7ImlkIjozNzkwMCwibmFtZSI6IiIsInN1Yl9kb21haW4iOiJwYXJ0bmVyLWRldiIsImxvZ28iOiJodHRwczovL3MzLmFwLXNvdXRoLTEuYW1hem9uYXdzLmNvbS9jZG4tc2hlYmFkZXYvaW1hZ2VzL3BhcnRuZXJzL2xvZ29zLzE1ODk5OTQzMjNfcGFydG5lcmRldi5wbmciLCJpc19tYW5hZ2VyIjp0cnVlfX0sInBhcnRuZXIiOm51bGwsIm1lbWJlciI6bnVsbCwiYnVzaW5lc3NfbWVtYmVyIjpudWxsLCJhZmZpbGlhdGUiOnsiaWQiOjM5MzI0fSwibG9naXN0aWNfdXNlciI6bnVsbCwiYmFua191c2VyIjpudWxsLCJzdHJhdGVnaWNfcGFydG5lcl9tZW1iZXIiOm51bGwsImF2YXRhciI6eyJ0eXBlIjoicGFydG5lciIsInR5cGVfaWQiOjM3OTAwfSwiZXhwIjoxNjIzMzIxMTM0LCJzdWIiOjI1OTkzMywiaXNzIjoiaHR0cDovL2FjY291bnRzLmRldi1zaGViYS54eXovYXBpL3YzL3Rva2VuL2dlbmVyYXRlIiwiaWF0IjoxNjIyNzE2MzM1LCJuYmYiOjE2MjI3MTYzMzUsImp0aSI6InBYeERYNjh5RUJ6NDRGanoifQ.ED1ln6pDSUla1pR96GhigvXSPt5_unRrvVzE5V6edgs';
    private $base_url = 'http://api.sheba.test';
    public function test_due_tracker_list()
    {
        $response = $this->get(
            $this->base_url . '/v2/accounting/due-tracker/due-list',
            [
                'Authorization' => $this->token
            ]
        );
        $response->assertResponseOk();
        $response->seeJsonStructure(
            [
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
                        'mobile'
                    ]
                ]
            ]
        );
    }
}