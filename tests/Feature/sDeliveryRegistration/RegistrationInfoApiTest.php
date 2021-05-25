<?php


namespace Tests\Feature\sDeliveryRegistration;


use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\Profile;
use App\Models\Resource;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use Tests\Feature\FeatureTestCase;

class RegistrationInfoApiTest extends FeatureTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->logIn();

        $this->truncateTables([
            Model ::class,
            Profile::class,
            Partner::class,
            Resource::class,
            PartnerResource::class
        ]);
//        $this->profile = factory(Profile::class)->create();
//        $this->partner = factory(Partner::class)->create();
//        $this->resource = factory(Resource::class)->create();
//        $this->partner_resource = factory(PartnerResource::class)->create();
        $this->partnerDeliveryinfo = factory(Model::class)->create();

    }

    public function testIsDeliveryRegisteredKeyOnDashboardApi()
    {
        //dd($this->resource->remember_token);

        $response = $this->get('v2/partners/'.$this->partner->id.'/dashboard?remember_token='.$this->resource->remember_token);
        $data = $response->decodeResponseJson();
        dd($data);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful", $data['message']);
    }
}