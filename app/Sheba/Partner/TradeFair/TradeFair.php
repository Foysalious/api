<?php namespace App\Sheba\Partner\TradeFair;


use App\Models\Partner;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\TradeFair\Model as TradeFairModel;

class TradeFair
{

    /**
     * @return mixed
     */
    public function getBusinessTypeWisePartner()
    {
        return DB::select('SELECT partners.id,partners.business_type
                                FROM   partners  JOIN (
                                SELECT  business_type, GROUP_CONCAT(id) grouped_partner
                                FROM  partners
                                WHERE EXISTS(SELECT id FROM trade_fair WHERE partner_id=partners.id AND is_published=1)
                                and  is_webstore_published = 1
                                GROUP BY business_type) group_max 
                                ON partners.business_type = group_max.business_type
                                AND FIND_IN_SET(id, grouped_partner) BETWEEN 1 AND 3
                                ORDER BY   partners.business_type DESC');
    }

    /**
     * @param $index
     * @return array
     */
    public function convertPartnerBusinessType($index = 'en')
    {
        $business_types = constants('PARTNER_BUSINESS_TYPE');
        $converted_business_types = [];
        foreach ($business_types as $business_type) {
            if ($index == 'bn')
                $converted_business_types[$business_type['bn']] = $business_type['en'];
            else
                $converted_business_types[$business_type['en']] = $business_type['bn'];
        }
        return $converted_business_types;
    }

    /**
     * @param $partners
     * @param $mapped_partner_business_type
     * @return mixed
     */
    public function makeData($partners, $mapped_partner_business_type)
    {
        $converted_business_types = $this->convertPartnerBusinessType('bn');
        $trade_fair_data = TradeFairModel::whereIn('partner_id', $partners)->with('partner', 'partner.webstoreBanner')->get()->map(function ($shop) use ($mapped_partner_business_type, $converted_business_types) {
            return [
                'stall_id' => $shop->stall_id,
                'partner_id' => $shop->partner_id,
                'partner_name' => $shop->partner->name,
                'sub_domain' => $shop->partner->sub_domain,
                'delivery_charge' => $shop->partner->delivery_charge,
                'banner' => $shop->partner->webstoreBanner ? [
                    'image_link' => $shop->partner->webstoreBanner->banner->image_link,
                    'small_image_link' => $shop->partner->webstoreBanner->banner->small_image_link,
                    'title' => $shop->partner->webstoreBanner->title,
                    'description' => $shop->partner->webstoreBanner->description
                ] : null,
                'description' => $shop->description,
                'discount' => $shop->discount,
                'is_published' => $shop->is_published,
                'business_type' => $converted_business_types[$mapped_partner_business_type[$shop->partner_id]],
            ];
        });

        $data = [];
        $stores = [];
        $trade_fair_data = collect($trade_fair_data)->groupBy('business_type');
        foreach ($trade_fair_data as $key => $value) {
            $stores['business_type'] = $key;
            $stores['stores'] = $value;
            array_push($data, $stores);
        }
        return $data;

    }

    /**
     * @param $business_type
     * @return array
     */
    public function getStoresByBusinessType($business_type)
    {
        $converted_business_types = $this->convertPartnerBusinessType();

        $partners = Partner::where(function ($q) {
            $q->whereHas('tradeFair', function ($tradeFair) {
                $tradeFair->where('is_published', 1);
            });
        })->with('tradeFair')->where('is_webstore_published', 1)
            ->where('business_type', $converted_business_types[$business_type])
            ->select('id', 'name', 'sub_domain', 'delivery_charge')->get();

        $stores = [];
        $partners->each(function ($partner) use (&$stores) {
            array_push($stores, [
                'stall_id' => $partner->tradeFair->stall_id,
                'partner_id' => $partner->id,
                'partner_name' => $partner->name,
                'sub_domain' => $partner->sub_domain,
                'delivery_charge' => $partner->delivery_charge,
                'banner' => $partner->webstoreBanner ? [
                    'image_link' => $partner->webstoreBanner->banner->image_link,
                    'small_image_link' => $partner->webstoreBanner->banner->small_image_link,
                    'title' => $partner->webstoreBanner->title,
                    'description' => $partner->webstoreBanner->description
                ] : null,
                'description' => $partner->tradeFair->description,
                'discount' => $partner->tradeFair->discount,
                'is_published' => $partner->tradeFair->is_published,
            ]);
        });
        return $stores;
    }


}