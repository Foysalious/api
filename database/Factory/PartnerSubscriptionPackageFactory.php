<?php namespace Factory;

use App\Models\PartnerSubscriptionPackage;

class PartnerSubscriptionPackageFactory extends Factory
{

    protected function getModelClass()
    {
        return PartnerSubscriptionPackage::class;
    }

    protected function getData()
    {
        return array_merge($this->commonSeeds, [
            'name'=> 'Free',
            'status'=> 'published',
            'rules'=> '{"resource_cap":{"value":3,"is_published":1},"commission":{"value":0,"is_published":1},"fee":{"monthly":{"value":0,"is_published":1},"yearly":{"value":0,"is_published":1},"half_yearly":{"value":0,"is_published":1}},"access_rules":{"loan":true,"dashboard_analytics":false,"pos":{"invoice":{"print":false,"download":false},"due":{"alert":false,"ledger":false},"inventory":{"warranty":{"add":true}},"report":true,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":false,"movie":false,"transport":false,"utility":false},"resource":{"type":{"add":false}},"expense":false,"extra_earning_global":false,"customer_list":false,"marketing_promo":false,"digital_collection":false,"old_dashboard":false,"notification":true,"eshop":false,"emi":false,"due_tracker":false},"tags":{"monthly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"},"yearly":{"bn":"\u09e8\u09eb% \u099b\u09be\u09dc","en":"25% discount","amount":"30,000"},"half_yearly":{"bn":"\u09eb\u09e6% \u099b\u09be\u09dc","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"\u09ae\u09be\u09b8\u09bf\u0995","price":0,"duration":30,"is_published":0},{"title":"yearly","title_bn":"\u09ac\u09be\u09ce\u09b8\u09b0\u09bf\u0995","price":0,"duration":365,"is_published":1},{"title":"half_yearly","title_bn":"\u0985\u09b0\u09cd\u09a7 \u09ac\u09be\u09b0\u09cd\u09b7\u09bf\u0995","price":0,"duration":182,"is_published":1}]}',
            'new_rules' => '{"resource_cap":{"value":3,"is_published":1},"commission":{"value":0,"is_published":1},"fee":{"monthly":{"value":0,"is_published":1},"yearly":{"value":0,"is_published":1},"half_yearly":{"value":0,"is_published":1}},"access_rules":{"loan":true,"dashboard_analytics":true,"pos":{"invoice":{"print":false,"download":false},"due":{"alert":false,"ledger":false},"inventory":{"warranty":{"add":true}},"report":true,"ecom":{"product_publish":false,"webstore_publish":true}},"extra_earning":{"topup":false,"movie":false,"transport":false,"utility":false},"resource":{"type":{"add":false}},"expense":false,"extra_earning_global":false,"customer_list":false,"marketing_promo":false,"digital_collection":false,"old_dashboard":false,"notification":true,"eshop":false,"emi":false,"due_tracker":false},"tags":{"monthly":{"bn":"৫০% ছাড়","en":"50% discount","amount":"540"},"yearly":{"bn":"২৫% ছাড়","en":"25% discount","amount":"30,000"},"half_yearly":{"bn":"৫০% ছাড়","en":"50% discount","amount":"540"}},"subscription_fee":[{"title":"monthly","title_bn":"মাসিক","price":0,"duration":30,"is_published":0},{"title":"yearly","title_bn":"বাৎসরিক","price":0,"duration":365,"is_published":1},{"title":"half_yearly","title_bn":"অর্ধ বার্ষিক","price":0,"duration":182,"is_published":1}]}'
        ]);
    }
}
