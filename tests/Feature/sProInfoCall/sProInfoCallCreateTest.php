<?php

namespace Tests\Feature\sProInfoCall;

use Sheba\Dal\InfoCall\InfoCall;
use Sheba\Dal\InfoCallRejectReason\InfoCallRejectReason;
use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLog;
use Sheba\Dal\ResourceTransaction\Model;
use Tests\Feature\FeatureTestCase;
use Throwable;

/**
 * @author Mahanaz Tabassum <mahanaz.tabassum@sheba.xyz>
 */
class InfoCallCreateTest extends FeatureTestCase
{
    /**
     * @throws Throwable
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->truncateTable(InfoCall::class);

        $this->truncateTable(InfoCallRejectReason::class);

        $this->truncateTable(InfoCallStatusLog::class);

        $this->truncateTable(Model::class);

        $this->logIn();
    }

    public function testInfoCallCreateResourceForResponse200()
    {
        $response = $this->post("v2/resources/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '01620011019',
            'location_id'  => 4,
        ],
        [
            'Authorization' => "Bearer $this->token",
        ]
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testResourceInfoCallCreateResponseWithoutLocationIdKey()
    {
        $response = $this->post("v2/resources/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '01620011019'
        ],
            [
                'Authorization' => "Bearer $this->token",
            ]
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testResourceInfoCallCreateResponseWithoutMobile()
    {
        $response = $this->post("v2/resources/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '',
            'location_id'  => 4,
        ],
            [
                'Authorization' => "Bearer $this->token",
            ]
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile field is required.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithoutServiceNameKey()
    {
        $response = $this->post("v2/resources/info-call", [
            'mobile'       => '01620011019',
            'location_id'  => 4,
        ],
            [
                'Authorization' => "Bearer $this->token",
            ]
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testInfoCallCreateResponseWithOnlyMobileNumber()
    {
        $response = $this->post("v2/resources/info-call", [
            'mobile'       => '01620011019'
        ],
            [
                'Authorization' => "Bearer $this->token",
            ]
        );
        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testResourceInfoCallCreateForResponse200WithCountryCodeMobile()
    {
        $response = $this->post("v2/resources/info-call", [
            'service_name' => 'Hand maid pizza',
            'mobile'       => '01620011019',
            'location_id'  => 4,
        ],
            [
                'Authorization' => "Bearer $this->token",
            ]
        );

        $data = $response->decodeResponseJson();

        $this->assertEquals(200, $data["code"]);
        $this->assertEquals("Successful", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testResourceInfoCallCreateResponseWithWrongLocationId()
    {
        $response = $this->post("v2/resources/info-call", [
            'mobile'       => '01620011019',
            'location_id'  => 672,
        ],
            [
                'Authorization' => "Bearer $this->token",
            ]
        );
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The selected location id is invalid.", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testResourceInfoCallCreateResponseWithCharactersInMobileNumber()
    {
        $response = $this->post("v2/resources/info-call", [
            'mobile'  => 'abc4562*&^'
        ],
            [
                'Authorization' => "Bearer $this->token",
            ]
        );
        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile is an invalid bangladeshi number .", $data["message"]);
    }

    /**
     * @throws Throwable
     */
    public function testResourceInfoCallCreateResponseWithWrongServiceNameMobileLocationId()
    {
        $response = $this->post("v2/resources/info-call", [
            'service_name' => '',
            'mobile'       => '',
            'location_id'  => '',
        ],
            [
                'Authorization' => "Bearer $this->token",
            ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(400, $data["code"]);
        $this->assertEquals("The mobile field is required.", $data["message"]
        );
    }

    /**
     * @throws Throwable
     */
    public function testResourceInfoCallCreateWithoutJWT()
    {
        $response = $this->post("v2/resources/info-call", [
            'mobile'       => '01620011017'
        ]);

        $data = $response->decodeResponseJson();

        $this->assertEquals(401, $data["code"]);
        $this->assertEquals("Your session has expired. Try Login", $data["message"]);
    }

}

