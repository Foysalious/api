<?php namespace Tests\Feature\TopUp;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Sheba\Dal\TopUpBlacklistNumber\TopUpBlacklistNumber;
use Sheba\Dal\TopUpOTFSettings\Model as TopUpOTFSettings;
use Sheba\Dal\TopUpVendorOTF\Model as TopUpVendorOTF;
use Sheba\Dal\TopUpVendorOTFChangeLog\Model as TopUpVendorOTFChangeLog;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\VerifyPin;
use Sheba\TopUp\TopUpExcel;
use Tests\Feature\FeatureTestCase;

class SbusinessBulkTopupTest extends FeatureTestCase
{
    /** @var Excel $excel */
    private $excel;

    /** @var $topUpVendor */
    private $topUpVendor;

    /** @var $topUpVendorCommission */
    private $topUpVendorCommission;

    /** @var $topUpOtfSettings */
    private $topUpOtfSettings;

    /** @var $topUpVendorOtf */
    private $topUpVendorOtf;

    /** @var $topUpStatusChangeLog */
    private $topUpStatusChangeLog;

    /** @var $topBlocklistNumbers */
    private $topBlocklistNumbers;

    /** @var $excelFileName */
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

        /**
         * TODO create topup topBlocklistNumbers table
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
        Business::find(1)->update(["wallet" => 1000]);
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
        Business::find(1)->update(["wallet" => 1000]);
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
        Business::find(1)->update(["wallet" => 4000]);
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
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
        $excel_item = $this->downloadExcelFile($data['excel_errors']);
        $this->assertEquals('Amount Should be Integer', $excel_item['+8801620011019']);

    }

    public function testBulkTopupMobileNumberInvalidAndAmountShouldNotBeIntegerNonIntegerResponse()
    {
        Business::find(1)->update(["wallet" => 5000]);
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
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
        $excel_item = $this->downloadExcelFile($data['excel_errors']);
        $this->assertEquals('Mobile number Invalid, Amount Should be Integer', $excel_item['+88016200110197896']);
    }

    public function testBulkTopupAmountExceededTopUpPrepaidLimitExitResponse()
    {
        Business::find(1)->update(["wallet" => 1000]);
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
        Business::find(1)->update(["wallet" => 7000]);
        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 2000
            ], [
                'mobile' => '+8801620011020',
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
        $this->assertEquals(420, $data['code']);
        $this->assertEquals("Check The Excel Data Format Properly.", $data['message']);
        $excel_item = $this->downloadExcelFile($data['excel_errors']);
        $this->assertEquals('The amount exceeded your topUp prepaid limit', $excel_item['+8801620011019']);
    }

    /**
    * API Failed to handle minimum amount error
    */

    public function testBulkMinTopupAmountExceededTopUpPrepaidLimitResponse()
    {
        Business::find(1)->update(["wallet" => 1000]);

        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 5
            ], [
                'mobile' => '+8801620011020',
                'operator' => 'MOCK',
                'connection_type' => 'prepaid',
                'amount' => 5
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

    public function testBulkTopupAFileExtensionResponse()
    {
        Business::find(1)->update(["wallet" => 2000]);
        $file = $this->getFileForUploadWrongExtention([
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
        $this->assertEquals(400, $data['code']);
        $this->assertEquals("File type not support", $data['message']);
    }

    /**
     * Bulk topup API support Excel without vendor field,
     *
     */

     public function testBulkTopupNonVendorResponse()
    {
        Business::find(1)->update(["wallet" => 2000]);
        $file = $this->getFileForUpload([
            [
                'mobile' => '+8801620011019',
                'connection_type' => 'prepaid',
                'amount' => 1000
            ], [
                'mobile' => '+8801620011020',
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
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Your top-up request has been received and will be transferred and notified shortly.", $data['message']);
    }

    /**
     * Bulk topup API support Excel without Amount field,
     */

     public function testBulkTopupNonAmountResponse()
    {
        Business::find(1)->update(["wallet" => 2000]);

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
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Your top-up request has been received and will be transferred and notified shortly.", $data['message']);
    }

    /**
     * Bulk topup API support Excel without Mobile Number field,
     */

     public function testBulkTopupNonNumberResponse()
    {
        Business::find(1)->update(["wallet" => 2000]);
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
        $this->assertEquals(200, $data['code']);
        $this->assertEquals("Your top-up request has been received and will be transferred and notified shortly.", $data['message']);
    }

    public function testSuccessfulBulkTopupSheetNameErrorResponse()
    {
        Business::find(1)->update(["wallet" => 1000]);
        $file = $this->getFileForUploadErrorSheetName([
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

    private function getFileForUploadWrongExtention(array $data)
    {
        $file = $this->getExcelFile($data)->save("CSV");
        $file_name = $file->getFileName() . '.' . $file->ext;
        $path = $file->storagePath . DIRECTORY_SEPARATOR . $file_name;
        return new UploadedFile($path, $file_name, null, null, null, true);
    }

    private function getFileForUploadErrorSheetName(array $data)
    {
        $file = $this->getExcelFileErrorSheetName($data)->save("xlsx");
        $file_name = $file->getFileName() . '.' . $file->ext;
        $path = $file->storagePath . DIRECTORY_SEPARATOR . $file_name;
        return new UploadedFile($path, $file_name, null, null, null, true);
    }

    private function getExcelFile(array $data)
    {
        return $this->excel->create($this->excelFileName, function (LaravelExcelWriter $excel) use ($data) {
            $excel->setTitle($this->excelFileName);

            $excel->sheet("data", function (LaravelExcelWorksheet $sheet) use ($data) {
                $sheet->fromArray($data);
            });
        });
    }

    private function getExcelFileErrorSheetName(array $data)
    {
        return $this->excel->create($this->excelFileName, function (LaravelExcelWriter $excel) use ($data) {
            $excel->setTitle($this->excelFileName);

                $excel->sheet("table", function (LaravelExcelWorksheet $sheet) use ($data) {
                $sheet->fromArray($data);
            });
        });
    }

    private function downloadExcelFile($error_data)
    {
        $file_name = basename($error_data);
        $file_name_with_folder = getStorageExportFolder() . $file_name;
        File::put($file_name_with_folder, file_get_contents($error_data));
        $excel_file = \Excel::selectSheets(TopUpExcel::SHEET)->load($file_name_with_folder)->get();
        $excel_item = [];
        $excel_file->each(function ($item, $key) use (&$excel_item) {
            $excel_item[$item->mobile] = $item[0];
        });
        unlink($file_name_with_folder);
        return $excel_item;
    }
}
