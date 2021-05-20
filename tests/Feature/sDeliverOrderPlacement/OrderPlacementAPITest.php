<?php


namespace Tests\Feature\sDeliverOrderPlacement;



use App\Models\PosOrder;
use App\Models\Profile;
use App\Models\TopUpVendor;
use Tests\Feature\FeatureTestCase;

class OrderPlacementAPITest extends FeatureTestCase
{

    private $PosOrderCreate;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            PosOrder::class,
            Profile::class
        ]);
        $this->logIn();


        $this->PosOrderCreate = factory(PosOrder::class)->create();
    }

    public function testDummy()
    {
    dd($this->PosOrderCreate);
        $this->assertEquals(1,1);
    }

}