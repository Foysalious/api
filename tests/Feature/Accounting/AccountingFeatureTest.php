<?php


namespace Tests\Feature\Accounting;


use GuzzleHttp\Client;

class AccountingFeatureTest extends \Tests\Feature\FeatureTestCase
{
    protected $token;

    protected function generateToken(): string
    {
        $client = new Client();
        $response = $client->get('https://accounts.dev-sheba.xyz/api/v3/token/generate?type=resource&token=TemAMQbHo8NES7nlEielwNw1EGTOKcQTC6jImGLNP4MLbFCjtvbeziGwlMd7&type_id=45320');
        $this->token = 'Bearer ' . \GuzzleHttp\json_decode($response->getBody())->token;
        return $this->token;
    }
}