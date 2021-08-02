<?php namespace Tests\Feature\TopUp;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Sheba\Dal\TopUpBlacklistNumber\TopUpBlacklistNumber;
use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;
use Sheba\Dal\TopUpVendorOTF\Model as TopUpVendorOTF;
use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\VerifyPin;
use Tests\Feature\FeatureTestCase;
use Maatwebsite\Excel\Excel;

class SbusinessBulkTopupTest extends FeatureTestCase
{
    /** @var Excel */
    private $excel;

    private $topUpVendor;
    private $topUpVendorCommission;
    private $topUpOtfSettings;
    private $topUpVendorOtf;
    private $topUpStatusChangeLog;
    private $topBlocklistNumbers;

    private $excelFileName;

    public function setUp()
    {
        parent::setUp();

        $this->excel = app(Excel::class);
        $this->excelFileName = "test_bulk_topup";

        $this->truncateTables([
            TopUpVendor::class,
            TopUpVendorCommission::class,
            TopUpOTFSettings::class,
            TopUpOrder::class,
            TopUpBlacklistNumber::class,
            Profile::class,
            BusinessMember::class,
            Business::class,
            Member::class
        ]);
        $this->logIn();

        $this->topUpVendor = factory(TopUpVendor::class)->create();
        $this->topUpVendorCommission = factory(TopUpVendorCommission::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id,
            'agent_commission' => '1.00',
            'type'=> "App\Models\Business"
        ]);


        $this->topUpOtfSettings = factory(TopUpOTFSettings::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        $this->topUpVendorOtf = factory(TopUpVendorOTF::class)->create([
            'topup_vendor_id' => $this->topUpVendor->id
        ]);

        $this->topUpStatusChangeLog= factory(TopUpVendorOTFChangeLog::class)->create([
            'otf_id' => $this->topUpVendorOtf->id
        ]);

        /*
         * TODO
         * create topup topBlocklistNumbers table
         */
        $this->topBlocklistNumbers= factory(TopUpBlacklistNumber::class)->create();

        $verify_pin_mock = $this->getMockBuilder(VerifyPin::class)
            ->setConstructorArgs([$this->app->make(AccountServer::class)])
            ->setMethods(['verify'])
            ->getMock();
        $verify_pin_mock->method('setAgent')->will($this->returnSelf());
        $verify_pin_mock->method('setProfile')->will($this->returnSelf());
        $verify_pin_mock->method('setRequest')->will($this->returnSelf());

        $this->app->instance(VerifyPin::class, $verify_pin_mock);
    }

    public function testSuccessfulBulkTopupResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 1000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 100
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 100
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Your top-up request has been received and will be transferred and notified shortly.", $data['message']);
    }

    public function testBulkTopupInvalidNumberResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 1000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+880162001101934534',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 100
            ], [
                'mobile' => '+880162001102034534',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 100
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);

    }

    public function testBulkTopupNonIntegerResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 4000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 2000.98
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 1000.98
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);

    }

    public function testBulkTopupMobileNumberInvalidAndAmountShouldNotbeIntegerNonIntegerResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 5000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+88016200110197896',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 2000.98
            ], [
                'mobile' => '+8801620011020987868',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 1000.98
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);

    }

    public function testBulkTopupAmountExceededTopUpPrepaidLimitExitResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 1000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(403, $data['code']);
        $this->assertEquals("You do not have sufficient balance to recharge.", $data['message']);
    }

    public function testBulkTopupMaximumAmountExceededTopUpPrepaidLimitResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 7000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 2000
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'C',
                'connection_type' => 'prepaid',
                'amount' => 2000
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
         // dd($data);
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
    }

    public function testBulkMinTopupAmountExceededTopUpPrepaidLimitResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 1000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 8
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 9
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
         //dd($data);
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
    }

    public function testBulkTopupAFileExtensionResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 2000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
    }
    public function testBulkTopupNonVendorResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 2000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                //'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ], [
                'mobile' => '+8801620011020',
               // 'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
    }

    public function testBulkTopupNonAmountResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 2000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid'
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid'
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
    }

    public function testBulkTopupNonNumberResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 2000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ], [
                'mobile' => '',
                'operator' => 'AIRTEL',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
    }

    public function testSuccessfulBulkTopupSheetNameErrorResponse()
    {
        $businessWallet = Business::find(1);;
        $businessWallet->update(["wallet" => 1000]);
        // dd($businessWallet);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 100
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 100
            ]
        ]);

        $response = $this->postWithFiles('/v2/top-up/business/bulk', [
            'is_otf_allow' => 0,
            'password' => 12345,
        ], [
            'file' => $file,
        ], [
            'Authorization' => "Bearer $this->token",
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("The sheet name used in the excel file is incorrect. Please download the sample excel file for reference.", $data['message']);
    }



    private function getFileForUpload(array $data)
    {
       $file = $this->getExcelFile($data)->save("xlsx");
        $file_name = $file->getFileName() . '.' . $file->ext;
        $path = $file->storagePath . DIRECTORY_SEPARATOR . $file_name;
        return new UploadedFile($path, $file_name, null, null, null, true);
    }

    private function getExcelFile(array $data)
    {
        return $this->excel->create($this->excelFileName, function (LaravelExcelWriter $excel) use ($data) {
            $excel->setTitle($this->excelFileName);

            $excel->sheet("data", function (LaravelExcelWorksheet $sheet) use ($data) {
            //$excel->sheet("table", function (LaravelExcelWorksheet $sheet) use ($data) {
                $sheet->fromArray($data);
            });
        });
    }
}
