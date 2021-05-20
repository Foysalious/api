<?php


namespace Tests\Feature\sDeliverOrderPlacement;



use App\Models\PosOrder;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use App\Models\Profile;
use App\Models\TopUpVendor;
use Factory\PartnerDeliveryInfoFactory;
use Tests\Feature\FeatureTestCase;

class OrderPlacementAPITest extends FeatureTestCase
{

    private $posOrderCreate;
    private $partnerDeliveryinfo;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            PosOrder::class,
            Profile::class
        ]);
        $this->logIn();


        $this->posOrderCreate = factory(PosOrder::class)->create();
        $this->partnerDeliveryinfo = factory(Model::class)->create();
    }

    public function testDummy()
    {
        dd($this->partnerDeliveryinfo);
        $this->assertEquals(1,1);
    }

}