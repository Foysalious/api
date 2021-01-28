<?php namespace Tests\Feature\SmanagerRegistration;

use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\PartnerSubscriptionPackage;
use App\Models\Resource;
use App\Models\Tag;
use Sheba\ExpenseTracker\Repository\ExpenseTrackerClient;
use Sheba\Sms\SmsVendor;
use Sheba\TopUp\Vendor\Response\MockResponse;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockExpenseClient;
use Tests\Mocks\MockSmsVendor;

class RegistrationTest extends FeatureTestCase
{
    /** @var PartnerSubscriptionPackage  */
    private $freeSubscription;
    /** @var PartnerSubscriptionPackage  */
    private $basicSubscription;

    public function setUp()
    {
        parent::setUp();
        $this->truncateTables([
            PartnerSubscriptionPackage::class,
            Partner::class,
            Resource::class,
            PartnerResource::class,
            Tag::class
        ]);

        $this->logIn();
        $this->freeSubscription = factory(PartnerSubscriptionPackage::class)->create();
        $this->basicSubscription = factory(PartnerSubscriptionPackage::class)->create([
            'name' => "Basic"
        ]);
        Tag::create([
            'name' => "Subscription fee",
            'taggable_type' => 'App\\Models\\PartnerTransaction',
        ]);

        $this->app->singleton(SmsVendor::class, MockSmsVendor::class);
        $this->app->singleton(ExpenseTrackerClient::class, MockExpenseClient::class);
    }

    public function testRegistrationPartner()
    {
        $response= $this->post("v2/profile/registration/partner",[
            "name"=> "ZUBAYER",
            "company_name"=> "ZUBAYER TEST",
            "gender"=> "পুরুষ",
            "bussiness_type"=> "োবাইল এবং গ্যাজেট",
            "from"=> "manager-app"
        ],[
            'Authorization'=> "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        dd($data);

    } //Test case: Post api without a mandatory payload and check response
    public function testRegistrationPartnerwithWrongPayload()
    {
        $response= $this->post("v2/profile/registration/partner",[
            "name"=> "ZUBAYER",
            //"company_name"=> "ZUBAYER TEST",
            //"gender"=> "পুরুষ",
            "bussiness_type"=> "োবাইল এবং গ্যাজেট",
            "from"=> "manager-app"
        ],[
            'Authorization'=> "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(400,$data["code"]);
        //dd($data);

    }

    public function testRegistrationApiReturnsSuccessfulResponseCode()
    {
        $response = $this->post("v2/profile/registration/partner", [
            "business_type" => "মোবাইল এবং গ্যাজেট",
            "company_name" => "erp tety",
            "gender" => "পুরুষ",
            "from" => "manager-app",
            "name" => "razoan",
        ], [
            'Authorization' => "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        dd($data);

    }

}