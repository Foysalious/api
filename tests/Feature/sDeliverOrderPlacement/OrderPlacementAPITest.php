<?php


namespace Tests\Feature\sDeliverOrderPlacement;



use App\Models\Partner;
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
    private $Partner;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTables([
            PosOrder::class,
            Profile::class,
            Partner::class,
            Model::class
        ]);
        $this->logIn();

       $this->partnerDeliveryinfo = factory(Model::class)->create();
       $this->posOrderCreate = factory(PosOrder::class)->create();

    }

    public function testSuccessfulOrderPlaceForPos()
    {
        $response = $this->post('/v2/pos/delivery/orders', [
            'logistic_partner_id' => 1,
            'weight' => '2.5',
            'cod_amount' => 500,
            'partner_name' => 'Test',
            'partner_phone' => '01956154440',
            'pickup_address' => 'Dhanmondi',
            'pickup_thana' => 'Dhanmondi',
            "pickup_district"=> 'Dhanmondi',
            'customer_name' => 'Nawshin',
            'customer_phone' => '01620011019',
            'delivery_address' => 'bangla motor',
            'delivery_thana' => 'Ramna',
            'delivery_district' => 'Dhaka',
            'pos_order_id' => '1'

        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        dd($response->decodeResponseJson());

        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Successful.", $data['message']);
    }

}