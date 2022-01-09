<?php namespace Tests\Feature\CustomerTransactionWallet;

use Tests\Feature\FeatureTestCase;
use Sheba\Dal\PaymentGateway\Model as PaymentGateway;
use Throwable;

/**
 * @author Mahanaz Tabassum <mahanaz.tabassum@sheba.xyz>
 */
class CustomerTransactionWalletTest extends FeatureTestCase
{
    private $shebaCredit;
    private $bkash;
    private $nagad;
    private $cityBank;
    private $otherCards;

    public function setUp(): void
    {
        parent::setUp();

        $this->truncateTable(PaymentGateway::class);

        $this->shebaCredit = PaymentGateway::factory()->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'wallet',
            'name_en' => 'Sheba Credit',
            'asset_name' => 'sheba_credit',
            'cash_in_charge' => '1.0',
            'discount_message' => '',
            'order' => '1'
        ]);

        $this->bkash = PaymentGateway::factory()->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'bkash',
            'name_en' => 'bKash',
            'asset_name' => 'bkash',
            'cash_in_charge' => '2.0',
            'discount_message' => '',
            'order' => '2'
        ]);

        $this->nagad = PaymentGateway::factory()->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'nagad',
            'name_en' => 'Nagad',
            'asset_name' => 'nagad',
            'cash_in_charge' => '3.0',
            'discount_message' => '',
            'order' => '3'
        ]);

        $this->cityBank = PaymentGateway::factory()->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'cbl',
            'name_en' => 'City Bank (American Express)',
            'asset_name' => 'cbl',
            'cash_in_charge' => '4.0',
            'discount_message' => '',
            'order' => '4'
        ]);

        $this->otherCards = PaymentGateway::factory()->create([
            'service_type' => 'App\Models\Customer',
            'method_name' => 'online',
            'name_en' => 'Other Cards',
            'asset_name' => 'ssl',
            'cash_in_charge' => '5.0',
            'discount_message' => '',
            'order' => '5'
        ]);

    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForShebaCredit()
    {
        $this->shebaCredit -> update(['discount_message' => '10% discount on Sheba Credit!']);

        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('10% discount on Sheba Credit!', $data_main["payments"][0]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForBkash()
    {
        $this->bkash -> update(['discount_message' => 'Hi Pay with your bkash app Get upto 50% cashback!']);

        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('Hi Pay with your bkash app Get upto 50% cashback!', $data_main["payments"][1]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForNagad()
    {
        $this->nagad -> update(['discount_message' => 'Hi Pay with your nagad app Get upto 30% cashback!']);

        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('Hi Pay with your nagad app Get upto 30% cashback!', $data_main["payments"][2]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForCityBank()
    {
        $this->cityBank -> update(['discount_message' => '১০ হাজার টাকার মধ্যে ভালো মোবাইল ২০২১  ২০২১!']);

        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('১০ হাজার টাকার মধ্যে ভালো মোবাইল ২০২১  ২০২১!', $data_main["payments"][3]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckDiscountMessageForOtherCards()
    {
        $this->otherCards -> update(['discount_message' => 'Hi Pay with your card to Get upto 70% cashback']);

        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('Hi Pay with your card to Get upto 70% cashback', $data_main["payments"][4]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForShebaCredit()
    {
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][0]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForBkash()
    {
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][1]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForNagad()
    {
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][2]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForCityBank()
    {
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][3]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIToCheckEmptyDiscountMessageForOtherCards()
    {
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals(200, $data_main["code"]);
        $this->assertEquals('Successful', $data_main["message"]);
        $this->assertEquals('', $data_main["payments"][4]["discount_message"]);
    }

    /**
     * @throws Throwable
     */
    public function testCustomerTransactionWalletAPIWithInvalidUrl()
    {
        $response_main = $this->get('/v2/payments?payable_type=order');

        $data_main = $response_main->decodeResponseJson();

        $this->assertEquals('404 Not Found', $data_main["message"]);
    }
}
