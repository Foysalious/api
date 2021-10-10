<?php


namespace Tests\Feature\SubscriptionTest;


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

class Subscription_test extends FeatureTestCase
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

    public function testSubscriptionRules()
    {
        $resource=Resource::first();
        $resource_remembar_token=$resource->remember_token;
        $respose=$this->get("v2/partners/38420/subscriptions/all-packages?remember_token=$resource_remembar_token",
        [
            "version-code"=>"21121"
        ]);
        $data= $respose->decodeResponseJson();
        dd($data);

    }
}