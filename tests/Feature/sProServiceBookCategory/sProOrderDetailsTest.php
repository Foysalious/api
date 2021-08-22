<?php namespace Tests\Feature\sProServiceBookCategory;

use Sheba\Dal\Service\Service;
use Tests\Feature\FeatureTestCase;

class sProOrderDetailsTest extends FeatureTestCase
{
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Service::class);

        $this->logIn();

        $this->service = factory(Service::class)->create([
            'description_bn' => '["গরু একটি গৃহপালিত পশু।  গরু একটি গৃহপালিত পশু।"]',
            'category_id' => 1,
            'is_published_for_backend' => 1
        ]);

    }

    public function testSProOrderDetailsAPIWithValidAuthToken()
    {
        //arrange

        //act
        $response = $this->get('v3/spro/service/' . $this->service->id . '/instructions', [
            'Authorization' => "Bearer $this->token"
        ]);

        $data = $response->decodeResponseJson();
        dd($data);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals('Successful', $data["message"]);

    }

}
