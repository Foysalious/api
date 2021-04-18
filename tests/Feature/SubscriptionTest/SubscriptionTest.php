<?php namespace Tests\Feature\SubscriptionTest;


use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\PartnerSubscriptionPackage;
use App\Models\Resource;
use App\Models\Tag;
use Sheba\ExpenseTracker\Repository\ExpenseTrackerClient;
use Sheba\Sms\SmsVendor;
use Tests\Feature\FeatureTestCase;
use Tests\Mocks\MockExpenseClient;
use Tests\Mocks\MockSmsVendor;

class SubscriptionTest extends FeatureTestCase
{
    /** @var PartnerSubscriptionPackage  */
    private $freeSubscription;

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

        //$this->freeSubscription = factory(PartnerSubscriptionPackage::class)->create();
        $this->basicSubscription = factory(PartnerSubscriptionPackage::class)->create([
            'name' => "Basic",
            'rules' => '{"resource_cap":{"value":5,"is_published":1},"commission":{"value":20,"is_published":1},"fee":{"monthly":{"value":95,"is_published":1},"yearly":{"value":310,"is_published":1},"half_yearly":{"value":410,"is_published":0}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":true,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":false,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":false,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":95,"duration":30,"is_published":0},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":310,"duration":365,"is_published":0},{"title":"two_yearly","title_bn":"\u09a6\u09cd\u09ac\u09bf-\u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":735,"duration":730,"is_published":1},{"title":"3_monthly","title_bn":"\u09e9 \u09ae\u09be\u09b8","price":285,"duration":90,"is_published":0},{"title":"6_monthly","title_bn":"\u09ec \u09ae\u09be\u09b8","price":570,"duration":180,"is_published":0},{"title":"9_monthly","title_bn":"\u09ef \u09ae\u09be\u09b8","price":855,"duration":270,"is_published":0},{"title":"11_month","title_bn":"egaro mash","price":880,"duration":330,"is_published":1},{"title":"13_month","title_bn":"month","price":900,"duration":800,"is_published":1}]}',
            'new_rules' => '{"resource_cap":{"value":3,"is_published":1},"commission":{"value":0,"is_published":1},"fee":{"monthly":{"value":0,"is_published":1},"yearly":{"value":0,"is_published":1},"half_yearly":{"value":0,"is_published":1}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":false,"download":false},"due":{"alert":false,"ledger":false},"inventory":{"warranty":{"add":true}},"report":true,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":false,"movie":false,"transport":false,"utility":false},"resource":{"type":{"add":false}},"expense":false,"extra_earning_global":false,"customer_list":false,"marketing_promo":false,"digital_collection":false,"old_dashboard":false,"notification":true,"eshop":false,"emi":false,"due_tracker":false},"tags":{"monthly":{"bn":"৫০% ছাড়","en":"50% discount","amount":"540"},"yearly":{"bn":"২৫% ছাড়","en":"25% discount","amount":"30,000"},"half_yearly":{"bn":"৫০% ছাড়","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"মাসিক","price":0,"duration":30,"is_published":0},{"title":"yearly","title_bn":"বাৎসরিক","price":0,"duration":365,"is_published":1},{"title":"half_yearly","title_bn":"অর্ধ বার্ষিক","price":0,"duration":182,"is_published":1}]}',
        ]);
        $this->logIn();




        Tag::create([
            'name' => "Subscription fee",
            'taggable_type' => 'App\\Models\\PartnerTransaction',
        ]);

        $this->app->singleton(SmsVendor::class, MockSmsVendor::class);
        $this->app->singleton(ExpenseTrackerClient::class, MockExpenseClient::class);


    }



    public function testSubscriptionRules()
    {
        $partner=Partner::first();
        $partner_id=$partner->id;
        $resource=Resource::first();
        $resource_remembar_token=$resource->remember_token;

        $respose=$this->get("v2/partners/".$partner_id."/subscriptions/all-packages?remember_token=".$resource_remembar_token,
        [
            "version-code" => "21121"
        ]);
        $data= $respose->decodeResponseJson();
        //dd($data);
        $publication_status_monthly=$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [0] ['is_published'];
        $publication_status_yearly=$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [1] ['is_published'];
        $publication_status_two_yearly=$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [2] ['is_published'];
        //dd($data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['resource'] ['type'] ['add']);

        $this->assertEquals(200,$data ["code"]);
        $this->assertEquals(1,$data ['data'] ['subscription_package'] [0] ['id']);
        $this->assertEquals("Basic",$data ['data'] ['subscription_package'] [0] ['name']);
        $this->assertEquals(5,$data  ['data'] ['subscription_package'] [0] ['rules'] ['resource_cap'] ['value']);
        $this->assertEquals(20,$data ['data'] ['subscription_package'] [0] ['rules'] ['commission'] ['value']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['loan']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['dashboard_analytics']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['invoice'] ['print']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['invoice'] ['download']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['due'] ['alert']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['due'] ['ledger']);
        $this->assertEquals(false,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['ecom'] ['product_publish']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['ecom'] ['webstore_publish']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['resource'] ['type'] ['add']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['expense']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['extra_earning_global']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['customer_list']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['marketing_promo']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['digital_collection']);
        $this->assertEquals(false,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['old_dashboard']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['notification']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['eshop']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['emi']);
        $this->assertEquals(true,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['due_tracker']);

        if($publication_status_monthly == 1)
        {
            $this->assertEquals("monthly",$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [0] ['title']);
        }
        elseif ($publication_status_yearly == 1)
        {
            $this->assertEquals("yearly",$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [1] ['title']);
        }
        elseif ($publication_status_two_yearly == 1)
        {
           $this->assertEquals("two_yearly",$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [2] ['title']) ;
        }


    }

    public function testSubscriptionRulesDynamic(){

        $partner=Partner::first();
        $partner_id=$partner->id;
        $resource=Resource::first();
        $resource_remembar_token=$resource->remember_token;
        $subscriptionData=PartnerSubscriptionPackage::first();
        $subscription_rules=json_decode($subscriptionData->rules,1);

        //dd($subscription_rules ['resource_cap'] ['value']);

        $respose=$this->get("v2/partners/".$partner_id."/subscriptions/all-packages?remember_token=".$resource_remembar_token,
            [
                "version-code" => "21121"
            ]);
        $data= $respose->decodeResponseJson();
        $publication_status_monthly=$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [0] ['is_published'];
        $publication_status_yearly=$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [1] ['is_published'];
        $publication_status_two_yearly=$data ['data'] ['subscription_package'] [0] ['rules'] ['subscription_fee'] [2] ['is_published'];
        //dd($data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['resource'] ['type'] ['add']);

        $this->assertEquals(200,$data ["code"]);
        $this->assertEquals(1,$data ['data'] ['subscription_package'] [0] ['id']);
        $this->assertEquals("Basic",$data ['data'] ['subscription_package'] [0] ['name']);
        $this->assertEquals($subscription_rules ['resource_cap'] ['value'],$data  ['data'] ['subscription_package'] [0] ['rules'] ['resource_cap'] ['value']);
        $this->assertEquals($subscription_rules ['commission'] ['value'],$data ['data'] ['subscription_package'] [0] ['rules'] ['commission'] ['value']);
        $this->assertEquals($subscription_rules ['access_rules'] ['loan'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['loan']);
        $this->assertEquals($subscription_rules ['access_rules'] ['dashboard_analytics'] ,$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['dashboard_analytics']);
        $this->assertEquals($subscription_rules ['access_rules'] ['pos'] ['invoice'] ['print'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['invoice'] ['print']);
        $this->assertEquals($subscription_rules ['access_rules'] ['pos'] ['invoice'] ['download'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['invoice'] ['download']);
        $this->assertEquals($subscription_rules ['access_rules'] ['pos'] ['due'] ['alert'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['due'] ['alert']);
        $this->assertEquals($subscription_rules ['access_rules'] ['pos'] ['due'] ['ledger'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['due'] ['ledger']);
        $this->assertEquals($subscription_rules ['access_rules'] ['pos'] ['ecom'] ['product_publish'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['ecom'] ['product_publish']);
        $this->assertEquals($subscription_rules ['access_rules'] ['pos'] ['ecom'] ['webstore_publish'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['pos'] ['ecom'] ['webstore_publish']);
        $this->assertEquals($subscription_rules ['access_rules'] ['resource'] ['type'] ['add'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['resource'] ['type'] ['add']);
        $this->assertEquals($subscription_rules ['access_rules'] ['expense'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['expense']);
        $this->assertEquals($subscription_rules ['access_rules'] ['extra_earning_global'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['extra_earning_global']);
        $this->assertEquals($subscription_rules ['access_rules'] ['customer_list'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['customer_list']);
        $this->assertEquals($subscription_rules ['access_rules'] ['marketing_promo'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['marketing_promo']);
        $this->assertEquals($subscription_rules ['access_rules'] ['digital_collection'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['digital_collection']);
        $this->assertEquals($subscription_rules ['access_rules'] ['old_dashboard'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['old_dashboard']);
        $this->assertEquals($subscription_rules ['access_rules'] ['notification'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['notification']);
        $this->assertEquals($subscription_rules ['access_rules'] ['eshop'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['eshop']);
        $this->assertEquals($subscription_rules ['access_rules'] ['emi'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['emi']);
        $this->assertEquals($subscription_rules ['access_rules'] ['due_tracker'],$data ['data'] ['subscription_package'] [0] ['rules'] ['access_rules'] ['due_tracker']);

    }
}