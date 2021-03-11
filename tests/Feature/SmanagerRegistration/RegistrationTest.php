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
use Carbon\Carbon;

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
        $partner_registration=Partner::all();
        $today = Carbon::today();
        $next_billing_date = $today->copy()->addDays(30);
        dd($partner_registration);
        $this-> assertEquals(1,$partner_registration->id);
        $this-> assertEquals("ZUBAYER TEST",$partner_registration->name);
        $this-> assertEquals("zubayer-test",$partner_registration->sub_domain);
        $this-> assertEquals("+8801678242955",$partner_registration->mobile);
        $this-> assertEquals(1,$partner_registration->can_topup);
        $this-> assertEquals("Onboarded",$partner_registration->status);
        $this-> assertEquals(2,$partner_registration->package_id);
        $this-> assertEquals("monthly",$partner_registration->billing_type);
        $this-> assertEquals($today,$partner_registration->billing_start_date);
        $this-> assertEquals($today,$partner_registration->last_billed_date);
        if($partner_registration->billing_type=="monthly")
        {
            $this-> assertEquals($next_billing_date->toDateString(),$partner_registration->next_billing_date);
        }
        else{

            //dd($partner_registration->billing_type);
        }
        $this-> assertEquals("0.00",$partner_registration->wallet);
        $this-> assertEquals(3,$partner_registration->subscription_rules->resource_cap->value);
        $this-> assertEquals(3,$partner_registration->subscription_rules->access_rules->value);



    }
    //Test resource table data
    public function testPartnerRegistrationResourceCreate()
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
        $resource_registration=Resource::all();
        //dd($resource_registration[0]->id);
        $this-> assertEquals(1,$resource_registration[0]->id);
        $this-> assertEquals(1,$resource_registration[0]->profile_id);

    }

    //Test Partner_resource table data
    public function testPartnerRegistrationPartnerResourceCreate()
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
        $partner_resource=PartnerResource::all();
        //dd($partner_resource[1]->resource_type);
        $this-> assertEquals(1,$partner_resource[0]->resource_id);
        $this-> assertEquals(1,$partner_resource[1]->resource_id);
        $this-> assertEquals("Admin",$partner_resource[0]->resource_type);
        $this-> assertEquals("Handyman",$partner_resource[1]->resource_type);
        $this-> assertEquals(1,$partner_resource[0]->partner_id);
        $this-> assertEquals(1,$partner_resource[1]->partner_id);

    }

    //Test case: Post api without a mandatory payload and check response
    public function testRRegistrationPartnerwithWrongPayload()
    {

        $response= $this->post("v2/profile/registration/partner",[
            "name"=> "ZUBAYER",
            "company_name"=> "ZUBAYER TEST",
            //"gender"=> "পুরুষ",
            "bussiness_type"=> "োবাইল এবং গ্যাজেট",
            "from"=> "manager-app"
        ],[
            'Authorization'=> "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200,$data["code"]);
        //$this->assertEquals(400,$data["message"]);
        //dd($data);

    }

    public function testRRegistrationPartnerwithWrongPayloadinBussines_type()
    {

        $response= $this->post("v2/profile/registration/partner",[
            "name"=> "ZUBAYER",
            "company_name"=> "ZUBAYER TEST",
            //"gender"=> "পুরুষ",
            "bussiness_type"=> 123456,
            "from"=> "manager-app"
        ],[
            'Authorization'=> "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        $this->assertEquals(200,$data["code"]);
        $this->assertEquals("Successful",$data["message"]);
        //dd($data);

    }

    public function testRRegistrationPartnerWithEmptyPayload()
    {
        $response= $this->post("v2/profile/registration/partner",[
            "name"=> "",
            "company_name"=>"",
            //"gender"=> "",
            "bussiness_type"=> "",
            "from"=> ""
        ],[
            'Authorization'=> "Bearer $this->token"
        ]);
        $data = $response->decodeResponseJson();
        //dd($data);
        $this->assertEquals(400,$data["code"]);
        $this->assertEquals("The company name field is required.",$data["message"]);
        //dd($data);

    }


}