<?php


namespace Tests\Feature\sDeliveryRegistration;


use App\Models\PartnerResource;
use Tests\Feature\FeatureTestCase;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInfo;

class PartnerDashboardAPITest extends FeatureTestCase
{
    private $partnerDeliveryinfo;
    public function setUp()
    {
        parent::setUp();
        $this->logIn();
        $this->truncateTables([
            PartnerResource::class,
            PartnerDeliveryInfo::class
        ]);
        $this->partner_resource = factory(PartnerResource::class)->create([
            'partner_id'=> $this->partner->id,
            'resource_id'=>$this->resource->id,
            'resource_type'=>'Admin'
        ]);
        $this->partnerDeliveryInfo = factory(PartnerDeliveryInfo::class)->create([
           'partner_id'=> $this->partner->id
        ]);
    }

    public function testDashboardApiRespondsSuccessfully()
    {
        $response = $this->get('/v2/partners/'.$this->partner->id.'/dashboard?remember_token='.$this->resource->remember_token);
        $data = $response->decodeResponseJson();
        dd($data);
    }
}