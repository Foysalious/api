<?php


namespace Tests\Feature\sDeliveryRegistration;


use App\Models\PartnerPosService;
use App\Models\PosCategory;
use App\Models\PosOrder;
use Factory\PartnerDeliveryInfoFactory;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInfo;
use Tests\Feature\FeatureTestCase;

class WebstoreDashboardInfoApiTest extends FeatureTestCase

{
    public function setUp()
    {
        parent::setUp();
        $this->logIn();

        $this->truncateTables([
            PartnerDeliveryInfo ::class,
            PosCategory::class
        ]);
        $this->PosCategory=factory(PosCategory::class)->create();
        $this->partner_pos_services = factory(PartnerPosService::class)->create([
         'partner_id'=>$this->partner->id,
         'pos_category_id'=> $this->PosCategory->id
        ]);
        $this->partnerDeliveryinfo = factory(PartnerDeliveryInfo::class)->create([
            'partner_id' => $this->partner->id,
            'delivery_vendor' => 'paperfly'
        ]);

    }

    public function testSuccessfulResponseWhenPartnerIsRegisteredForDelivery()
    {
        $response = $this->get('/v2/partners/'.$this->partner->id.'/webstore-dashboard?remember_token='.$this->resource->remember_token.'&frequency=month&month=10&year=2020');
        $data = $response->decodeResponseJson();
        $this->assertEquals(200,$data['code']);
        $this->assertEquals("Successful",$data['message']);
        $this->assertEquals(1,$data['webstore_dashboard']['is_registered_for_delivery']);
    }

    public function testSuccessfulResponseWhenPartnerIsNotRegisteredForDelivery()
    {
        $this->truncateTable(PartnerDeliveryInfo::class);
        $response = $this->get('/v2/partners/'.$this->partner->id.'/webstore-dashboard?remember_token='.$this->resource->remember_token.'&frequency=month&month=10&year=2020');
        $data = $response->decodeResponseJson();
        $this->assertEquals(200,$data['code']);
        $this->assertEquals("Successful",$data['message']);
        $this->assertEquals(0,$data['webstore_dashboard']['is_registered_for_delivery']);
    }
}