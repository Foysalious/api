<?php


namespace Tests\Feature\sDeliverOrderPlacement;



use App\Models\PartnerPosService;
use App\Models\PosCategory;
use App\Models\PosOrder;
use PhpParser\Node\Expr\AssignOp\Mod;
use Sheba\Dal\PartnerDeliveryInformation\Model;
use App\Models\Profile;
use Sheba\Dal\PartnerPosCategory\PartnerPosCategory;
use Tests\Feature\FeatureTestCase;

class OrderPlacementAPITest extends FeatureTestCase
{

    private $posOrderCreate;
    private $partnerDeliveryinfo;
    private $partnerPosService;
    private $partnerPosCategory;
    private $posCategory;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            PosOrder::class,
            Profile::class,
            Model::class,
            PartnerPosService::class,
            PartnerPosCategory::class,
            PosCategory::class
        ]);
        $this->logIn();


     //  $this->posOrderCreate = factory(PosOrder::class)->create();
      //  $this->partnerDeliveryinfo = factory(Model::class)->create();
        $this->posCategory = factory(PosCategory::class)->create();
       $this->partnerPosCategory = factory(PartnerPosCategory::class)->create();
       $this->partnerPosService = factory(PartnerPosService::class)->create();
    }

    public function testDummy()
    {
        dd($this->partnerPosService);
        $this->assertEquals(1,1);
    }

}