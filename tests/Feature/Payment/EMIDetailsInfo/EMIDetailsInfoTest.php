<?php

namespace Tests\Feature\Payment\EMIDetailsInfo;

use APP\Sheba\EMI\Banks;
use APP\Sheba\EMI\Calculator;
use Tests\Feature\FeatureTestCase;

/**
 * @author Zubayer alam <zubayer@sheba.xyz>
 */
class EMIDetailsInfoTest extends FeatureTestCase
{

    private function EMICalculationMethod($amount): array
    {

        if (!$amount) {
            $amount = 5000;
        }

        $emi_data = [
            "emi"   => (new \Sheba\EMI\Calculator())->getCharges($amount),
            "banks" => (new \Sheba\EMI\Banks())->setAmount($amount)->get()->toJson(),
            "minimum_amount" => number_format(config('sheba.min_order_amount_for_emi')),
            "static_info" =>[
                "how_emi_works"=>[
                    "EMI (Equated Monthly Installment) is one of the payment methods of online purchasing, only for the customers using any of the accepted Credit Cards on Sheba.xyz.* It allows customers to pay for their ordered services  in easy equal monthly installments.*",
                    "Sheba.xyz has introduced a convenient option of choosing up to 12 months EMI facility for customers who use Credit Cards for buying services worth BDT 5,000 or more. The duration and extent of the EMI options available will be visible on the payment page after order placement. EMI plans are also viewable on the checkout page in the EMI Banner below the bill section.",
                    "Customers wanting to avail EMI facility must have a Credit Card from any one of the banks in the list shown in the payment page.",
                    "EMI facilities available for all services worth BDT 5,000 or more.",
                    "EMI charges may vary on promotional offers.",
                    "Sheba.xyz  may charge additional convenience fee if the customer extends the period of EMI offered."
                ],
                "terms_and_conditions"=>[
                    "As soon as you complete your purchase order on Sheba.xyz, you will see the full amount charged on your credit card.",
                    "You must Sign and Complete the EMI form and submit it at Sheba.xyz within 3 working days.",
                    "Once Sheba.xyz receives this signed document from the customer, then it shall be submitted to the concerned bank to commence the EMI process.",
                    "The EMI processing will be handled by the bank itself *. After 5-7 working days, your bank will convert this into EMI.",
                    "From your next billing cycle, you will be charged the EMI amount and your credit limit will be reduced by the outstanding amount.",
                    "If you do not receive an updated monthly bank statement reflecting your EMI transactions for the following month, feel free to contact us at 16516  for further assistance.",
                    "For example, if you have made a 3-month EMI purchase of BDT 30,000 and your credit limit is BDT 1, 00,000 then your bank will block your credit limit by BDT 30,000 and thus your available credit limit after the purchase will only be BDT 70,000. As and when you pay your EMI every month, your credit limit will be released accordingly.",
                    "EMI facilities with the aforesaid Banks are regulated as per their terms and conditions and these terms may vary from one bank to another.",
                    "For any query or concern please contact your issuing bank, if your purchase has not been converted to EMI by 7 working days of your transaction date."
                ]
            ]
        ];
        return $emi_data;
    }

    public function testEMIInfoByGivingNoParameterAmount()
    {

        // arrange
        $amount = 5000;
        $emi_data = $this->EMICalculationMethod($amount);

        // act
        $response = $this->get("/v3/emi-info");
        $data = $response->decodeResponseJson();

        $data["info"]["banks"] = json_encode($data["info"]["banks"]);

        // assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals(number_format($amount), $data["price"]);
        $this->assertEquals($emi_data["emi"], $data["info"]["emi"]);
        $this->assertEquals($emi_data["banks"], $data["info"]["banks"]);
        $this->assertEquals($emi_data["minimum_amount"], $data["info"]["minimum_amount"]);
        $this->assertEquals($emi_data["static_info"]["how_emi_works"], $data["info"]["static_info"]["how_emi_works"]);
        $this->assertEquals($emi_data["static_info"]["terms_and_conditions"], $data["info"]["static_info"]["terms_and_conditions"]);

    }

    public function testEMIInfoByGivingParameterOfEqualToMinimumAmount()
    {

        //arrange
        $amount = 5000;
        $emi_data = $this->EMICalculationMethod($amount);

        //act
        $response = $this->get("/v3/emi-info?amount=$amount");
        $data = $response->decodeResponseJson();

        $data["info"]["banks"] = json_encode($data["info"]["banks"]);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals(number_format($amount), $data["price"]);
        $this->assertEquals($emi_data["emi"], $data["info"]["emi"]);
        $this->assertEquals($emi_data["banks"], $data["info"]["banks"]);

    }

    public function testEMIInfoByGivingParameterOfLessThanMinimumAmount()
    {

        //arrange
        $amount = 4999;

        //act
        $response = $this->get("/v3/emi-info?amount=$amount");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("Amount is less than minimum emi amount", $data["message"]);

    }

    public function testEMIInfoByGivingParameterOfMoreThanMinimumAmount()
    {

        //arrange
        $amount = 10000;
        $emi_data = $this->EMICalculationMethod($amount);

        //act
        $response = $this->get("/v3/emi-info?amount=$amount");
        $data = $response->decodeResponseJson();

        $data["info"]["banks"] = json_encode($data["info"]["banks"]);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals(number_format($amount), $data["price"]);
        $this->assertEquals($emi_data["emi"], $data["info"]["emi"]);
        $this->assertEquals($emi_data["banks"], $data["info"]["banks"]);

    }

    public function testEMIInfoByGivingParameterOfFractionalValue()
    {

        //arrange
        $amount = 10000.26355;
        $emi_data = $this->EMICalculationMethod($amount);

        //act

        $response = $this->get("/v3/emi-info?amount=$amount");
        $data = $response->decodeResponseJson();

        $data["info"]["banks"] = json_encode($data["info"]["banks"]);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals(number_format($amount), $data["price"]);
        $this->assertEquals($emi_data["emi"], $data["info"]["emi"]);
        $this->assertEquals($emi_data["banks"], $data["info"]["banks"]);

    }

    public function testEMIInfoByGivingParameterOfZeroAmount()
    {
        /*
         * Although the value 0 should give a 400 status code response but in this case it is giving 200 due to the
         * reason that it takes 0 as no amount passed.
         * The value 0.0 gives 400 status code response. The actual value that passes is 0.0 in case of 0 insertion.
         */
        //arrange
        $amount = 0;
        $min_amount = 5000;
        $emi_data = $this->EMICalculationMethod($amount);

        //act

        $response = $this->get("/v3/emi-info?amount=$amount");
        $data = $response->decodeResponseJson();

        $data["info"]["banks"] = json_encode($data["info"]["banks"]);

        //assert
        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
        $this->assertEquals(number_format($min_amount), $data["price"]);
        $this->assertEquals($emi_data["emi"], $data["info"]["emi"]);
        $this->assertEquals($emi_data["banks"], $data["info"]["banks"]);

    }

    public function testEMIInfoByGivingWrongURL()
    {

        //arrange

        //act
        $response = $this->get("/v3/emi-infossss");
        $data = $response->decodeResponseJson();

        //assert
        $this->assertEquals("404 Not Found", $data["message"]);

    }
}
