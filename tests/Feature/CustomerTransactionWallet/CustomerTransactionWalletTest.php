<?php namespace Tests\Feature\CustomerTransactionWallet;

use Tests\Feature\FeatureTestCase;
use Sheba\Dal\PaymentGateway\Model;

class CustomerTransactionWalletTest extends FeatureTestCase
{

    private $shebaCredit;
    private $bkash;
    private $nagad;
    private $cityBank;
    private $otherCards;

    public function setUp()
    {
        parent::setUp();

        $this->truncateTable(Model::class);

        $this->shebaCredit = factory(Model::class)->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'wallet',
            'name_en' => 'Sheba Credit',
            'asset_name' => 'sheba_credit',
            'cash_in_charge' => '1.0',
            'discount_message' => '',
            'order' => '1'
        ]);

        $this->bkash = factory(Model::class)->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'bkash',
            'name_en' => 'bKash',
            'asset_name' => 'bkash',
            'cash_in_charge' => '2.0',
            'discount_message' => '',
            'order' => '2'
        ]);

        $this->nagad = factory(Model::class)->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'nagad',
            'name_en' => 'Nagad',
            'asset_name' => 'nagad',
            'cash_in_charge' => '3.0',
            'discount_message' => '',
            'order' => '3'
        ]);

        $this->cityBank = factory(Model::class)->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'cbl',
            'name_en' => 'City Bank (American Express)',
            'asset_name' => 'cbl',
            'cash_in_charge' => '4.0',
            'discount_message' => '',
            'order' => '4'
        ]);

        $this->otherCards = factory(Model::class)->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'online',
            'name_en' => 'Other Cards',
            'asset_name' => 'ssl',
            'cash_in_charge' => '5.0',
            'discount_message' => '',
            'order' => '5'
        ]);

    }

    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForShebaCredit()
    {
        //arrange
        $this->shebaCredit -> update(['discount_message' => '10% discount on Sheba Credit!']);

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('10% discount on Sheba Credit!', $data_main["payments"][0]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForBkash()
    {
        //arrange
        $this->bkash -> update(['discount_message' => 'Hi Pay with your bkash app Get upto 50% cashback!']);

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('Hi Pay with your bkash app Get upto 50% cashback!', $data_main["payments"][1]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForNagad()
    {
        //arrange
        $this->nagad -> update(['discount_message' => 'Hi Pay with your nagad app Get upto 30% cashback!']);

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('Hi Pay with your nagad app Get upto 30% cashback!', $data_main["payments"][2]["discount_message"]);
    }

    //Failed
    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForCityBank()
    {
        //arrange
        $this->cityBank -> update(['discount_message' => '১০ হাজার টাকার মধ্যে ভালো মোবাইল ২০২১  ২০২১!']);

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('১০ হাজার টাকার মধ্যে ভালো মোবাইল ২০২১  ২০২১!', $data_main["payments"][3]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForOtherCards()
    {
        //arrange
        $this->otherCards -> update(['discount_message' => 'Hi Pay with your card to Get upto 70% cashback']);

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('Hi Pay with your card to Get upto 70% cashback', $data_main["payments"][4]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForShebaCredit()
    {
        //arrange

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][0]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForBkash()
    {
        //arrange

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][1]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForNagad()
    {
        //arrange

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][2]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForCityBank()
    {
        //arrange

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][3]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForOtherCards()
    {
        //arrange

        //act
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][4]["discount_message"]);
    }

    public function testCustomerTransactionWalletAPIWithInvalidUrl()
    {
        //arrange

        //act
        $response_main = $this->get('/v2/paymentss?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        //assert
        $this->assertEquals('404 Not Found', $data_main["message"]);
    }

}
