<?php namespace Tests\Feature\CustomerTransactionWallet;

use Tests\Feature\FeatureTestCase;
use Sheba\Dal\PaymentGateway\Model;

class CustomerTransactionWalletTest extends FeatureTestCase
{

    private $model;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Model::class);

    }

    public function testCustomerTransactionWalletAPIForStatusCode200()
    {
        //arrange
        $this->model = factory(Model::class)->create();

        //act
        $response_main = $this->get('/v2/payments?payable_type=order&type=customer');

        $data_main = $response_main->decodeResponseJson();
        dd($data_main);

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
    }
}
