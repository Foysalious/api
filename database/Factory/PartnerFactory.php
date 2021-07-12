<?php namespace Factory;


use App\Models\Partner;

class PartnerFactory extends Factory
{

    protected function getModelClass()
    {
        return Partner::class; // TODO: Implement getModelClass() method.
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'name'=> $this->faker->name,
            'package_id'=>2,
            'mobile' => '+8801678242967',
            'password' => bcrypt(14725),
            'status' => 'Verified',
            'wallet' => 50000,
            'subscription_rules' =>'{"resource_cap":{"value":5,"is_published":1},"commission":{"value":20,"is_published":1},"fee":{"monthly":{"value":95,"is_published":1},"yearly":{"value":310,"is_published":1},"half_yearly":{"value":410,"is_published":0}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":true,"download":true},"due":{"alert":true,"ledger":true},"inventory":{"warranty":{"add":true}},"report":false,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":true,"movie":true,"transport":true,"utility":true},"resource":{"type":{"add":true}},"expense":true,"extra_earning_global":true,"customer_list":true,"marketing_promo":true,"digital_collection":true,"old_dashboard":false,"notification":true,"eshop":true,"emi":true,"due_tracker":true},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":95,"duration":30,"is_published":0},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":310,"duration":365,"is_published":0},{"title":"two_yearly","title_bn":"\u09a6\u09cd\u09ac\u09bf-\u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":735,"duration":730,"is_published":1},{"title":"3_monthly","title_bn":"\u09e9 \u09ae\u09be\u09b8","price":285,"duration":90,"is_published":0},{"title":"6_monthly","title_bn":"\u09ec \u09ae\u09be\u09b8","price":570,"duration":180,"is_published":0},{"title":"9_monthly","title_bn":"\u09ef \u09ae\u09be\u09b8","price":855,"duration":270,"is_published":0},{"title":"11_month","title_bn":"egaro mash","price":880,"duration":330,"is_published":1},{"title":"13_month","title_bn":"month","price":900,"duration":800,"is_published":1}]}',
            'billing_type' => "monthly"

        ]);// TODO: Implement getData() method.
    }
}