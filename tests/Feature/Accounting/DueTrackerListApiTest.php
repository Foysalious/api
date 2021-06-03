<?php


namespace Tests\Feature\Accounting;


use GuzzleHttp\Client;
use Tests\Feature\FeatureTestCase;

class DueTrackerListApiTest extends FeatureTestCase
{
    public function test_due_tracker_list()
    {
        $response = $this->get(
            config('sheba.api_url'). '/v2/accounting/due-tracker/due-list',
            [
                'Authorization' => $this->generateToken()
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

    private function generateToken(): string
    {
        $client = new Client();
        $response = $client->get('https://accounts.dev-sheba.xyz/api/v3/token/generate?type=resource&token=TemAMQbHo8NES7nlEielwNw1EGTOKcQTC6jImGLNP4MLbFCjtvbeziGwlMd7&type_id=45320');
        return 'Bearer ' . \GuzzleHttp\json_decode($response->getBody())->token;
    }
}