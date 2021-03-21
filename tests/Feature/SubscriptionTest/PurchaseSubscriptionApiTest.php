<?php

use App\Models\Bonus;
use App\Models\BonusLog;
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


class PurchaseSubscriptionApiTest extends FeatureTestCase{



    public function setUp()
    {

        parent::setUp();
        $this->truncateTables([
            PartnerSubscriptionPackage::class,
            Partner::class,
            Resource::class,
            PartnerResource::class,
            Tag::class,
            Bonus::class,
            BonusLog::class

        ]);


        //$this->freeSubscription = factory(PartnerSubscriptionPackage::class)->create();
        $this->basicSubscription = factory(PartnerSubscriptionPackage::class)->create([
            'name' => "Basic",
            'rules' => '{"resource_cap":{"value":5,"is_published":1},"commission":{"value":20,"is_published":1},"fee":{"monthly":{"value":95,"is_published":1},"yearly":{"value":310,"is_published":1},"half_yearly":{"value":410,"is_published":0}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":true,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":false,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":false,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":95,"duration":30,"is_published":0},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":310,"duration":365,"is_published":0},{"title":"two_yearly","title_bn":"\u09a6\u09cd\u09ac\u09bf-\u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":735,"duration":730,"is_published":1},{"title":"3_monthly","title_bn":"\u09e9 \u09ae\u09be\u09b8","price":285,"duration":90,"is_published":0},{"title":"6_monthly","title_bn":"\u09ec \u09ae\u09be\u09b8","price":570,"duration":180,"is_published":0},{"title":"9_monthly","title_bn":"\u09ef \u09ae\u09be\u09b8","price":855,"duration":270,"is_published":0},{"title":"11_month","title_bn":"egaro mash","price":880,"duration":330,"is_published":1},{"title":"13_month","title_bn":"month","price":900,"duration":800,"is_published":1}]}',
            'new_rules' => '{"resource_cap":{"value":3,"is_published":1},"commission":{"value":0,"is_published":1},"fee":{"monthly":{"value":0,"is_published":1},"yearly":{"value":0,"is_published":1},"half_yearly":{"value":0,"is_published":1}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":false,"download":false},"due":{"alert":false,"ledger":false},"inventory":{"warranty":{"add":true}},"report":true,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":false,"movie":false,"transport":false,"utility":false},"resource":{"type":{"add":false}},"expense":false,"extra_earning_global":false,"customer_list":false,"marketing_promo":false,"digital_collection":false,"old_dashboard":false,"notification":true,"eshop":false,"emi":false,"due_tracker":false},"tags":{"monthly":{"bn":"৫০% ছাড়","en":"50% discount","amount":"540"},"yearly":{"bn":"২৫% ছাড়","en":"25% discount","amount":"30,000"},"half_yearly":{"bn":"৫০% ছাড়","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"মাসিক","price":0,"duration":30,"is_published":0},{"title":"yearly","title_bn":"বাৎসরিক","price":0,"duration":365,"is_published":1},{"title":"half_yearly","title_bn":"অর্ধ বার্ষিক","price":0,"duration":182,"is_published":1}]}',
        ]);

        $this->AdvanceSubscription = factory(PartnerSubscriptionPackage::class)->create([
            'name' => "Advance",
            'rules' => '{"resource_cap":{"value":50,"is_published":1},"commission":{"value":15,"is_published":1},"fee":{"monthly":{"value":10500,"is_published":1},"yearly":{"value":94500,"is_published":1},"half_yearly":{"value":51030,"is_published":1}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":true,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":true,"ecom":{"product_publish":true,"product_publish_limit":10,"webstore_publish":false}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":true,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09e8\u09eb% \u099b\u09be\u09dc","en":"25% discount","amount":"30,000"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":10500,"duration":30,"is_published":1},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":94500,"duration":365,"is_published":1},{"title":"half_yearly","title_bn":"\u0985\u09b0\u09cd\u09a7 \u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":51030,"duration":182,"is_published":1}]}',
            'new_rules' => '{"resource_cap":{"value":50,"is_published":1},"commission":{"value":15,"is_published":1},"fee":{"monthly":{"value":10500,"is_published":1},"yearly":{"value":94500,"is_published":1},"half_yearly":{"value":51030,"is_published":1}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":true,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":true,"ecom":{"product_publish":true,"product_publish_limit":10,"webstore_publish":false}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":true,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09e8\u09eb% \u099b\u09be\u09dc","en":"25% discount","amount":"30,000"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":10500,"duration":30,"is_published":1},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":94500,"duration":365,"is_published":1},{"title":"half_yearly","title_bn":"\u0985\u09b0\u09cd\u09a7 \u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":51030,"duration":182,"is_published":1}]}',
        ]);

        $this->standardSubscription = factory(PartnerSubscriptionPackage::class)->create([
            'name' => "Standard",
            'rules' => '{"resource_cap":{"value":35,"is_published":1},"commission":{"value":25,"is_published":1},"fee":{"monthly":{"value":1575,"is_published":1},"yearly":{"value":10500,"is_published":1},"half_yearly":{"value":7665,"is_published":1}},"access_rules":{"loan":false,"dashboard_analytics":true,"pos":{"invoice":{"print":false,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":true,"ecom":{"product_publish":false,"product_publish_limit":10,"webstore_publish":false}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":true,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09ea\u09eb% \u099b\u09be\u09dc","en":"45% discount","amount":"8,000"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":1575,"duration":30,"is_published":1},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":10500,"duration":365,"is_published":0},{"title":"half_yearly","title_bn":"\u0985\u09b0\u09cd\u09a7 \u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":7665,"duration":182,"is_published":0},{"title":"two_yearly","title_bn":"\u09a6\u09cd\u09ac\u09bf-\u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":10500,"duration":730,"is_published":0}]}',
            'new_rules' => '{"resource_cap":{"value":35,"is_published":1},"commission":{"value":25,"is_published":1},"fee":{"monthly":{"value":1575,"is_published":1},"yearly":{"value":10500,"is_published":1},"half_yearly":{"value":7665,"is_published":1}},"access_rules":{"loan":false,"dashboard_analytics":true,"pos":{"invoice":{"print":false,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":true,"ecom":{"product_publish":false,"product_publish_limit":10,"webstore_publish":false}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":true,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09ea\u09eb% \u099b\u09be\u09dc","en":"45% discount","amount":"8,000"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":1575,"duration":30,"is_published":1},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":10500,"duration":365,"is_published":0},{"title":"half_yearly","title_bn":"\u0985\u09b0\u09cd\u09a7 \u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":7665,"duration":182,"is_published":0},{"title":"two_yearly","title_bn":"\u09a6\u09cd\u09ac\u09bf-\u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":10500,"duration":730,"is_published":0}]}',
        ]);
        $this->logIn();

        Tag::create([
            'name' => "Subscription fee",
            'taggable_type' => 'App\\Models\\PartnerTransaction',
        ]);

        $this->app->singleton(SmsVendor::class, MockSmsVendor::class);
        $this->app->singleton(ExpenseTrackerClient::class, MockExpenseClient::class);

    }
    public function testSubscriptionPurchase(){
        $partner=Partner::first();
        $partner_id=$partner->id;
        $partner_wallet=$partner->wallet;
        //dd($partner_wallet);
        $resource=Resource::first();
        $resource_remembar_token=$resource->remember_token;
        $partner_transactions=
        //dd($partner_transactions);


        $response = $this->post( "v2/partners/".$partner_id."/subscriptions/purchase",[

                "remember_token"=>$resource_remembar_token,
                "package_id"=>2,
                "billing_type"=>"monthly"
            ]
        );

        $data=$response->decodeResponseJson();
         dd($data);

        $this->assertEquals(200,$data['code']);
        $this->assertEquals(10500,$data['price']);
        $this->assertEquals(39500,$data['remaining_balance']);



    }

    public function testSubscriptionPurchaseWithBonusWallet(){
        $partner=Partner::first();
        $partner_id=$partner->id;
        $partner_wallet=$partner->wallet;
        //dd($partner_wallet);
        $resource=Resource::first();
        $resource_remembar_token=$resource->remember_token;
        // dd($partner_transactions);


        $response = $this->post( "v2/partners/".$partner_id."/subscriptions/purchase",[

                "remember_token"=>$resource_remembar_token,
                "package_id"=>2,
                "billing_type"=>"monthly"
            ]
        );

        $data=$response->decodeResponseJson();
        dd($data);
        $partner_bonus_transaction=Bonus::first();
        $partner_bonus_transaction_logs=BonusLog::first();
        //dd($partner_bonus_transaction_logs);
       /* $this->assertEquals(200,$data['code']);
        $this->assertEquals(10500,$data['price']);
        $this->assertEquals(39500,$data['remaining_balance']);*/



    }

    public function testSubscriptionPurchaseWithPartialWallet(){
        $partner=Partner::first();
        $partner_id=$partner->id;
        $partner_wallet=$partner->wallet;
        //dd($partner_wallet);
        $resource=Resource::first();
        $resource_remembar_token=$resource->remember_token;
        // dd($partner_transactions);


        $response = $this->post( "v2/partners/".$partner_id."/subscriptions/purchase",[

                "remember_token"=>$resource_remembar_token,
                "package_id"=>2,
                "billing_type"=>"monthly"
            ]
        );

        $data=$response->decodeResponseJson();
        dd($data);
        $partner_bonus_transaction=Bonus::first();
        $partner_bonus_transaction_logs=BonusLog::first();
        //dd($partner_bonus_transaction_logs);
        /* $this->assertEquals(200,$data['code']);
         $this->assertEquals(10500,$data['price']);
         $this->assertEquals(39500,$data['remaining_balance']);*/



    }
}
