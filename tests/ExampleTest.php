<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    protected $client;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://api.sheba.new/v1/'
        ]);
    }

    /**
     * A basic api test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $response = $this->client->get('/');
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('msg', $data);
        $this->assertEquals("Success. This project will hold the api's", $data['msg']);
    }
}
