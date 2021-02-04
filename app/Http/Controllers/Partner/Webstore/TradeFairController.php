<?php namespace App\Http\Controllers\Partner\Webstore;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\TradeFair\Model as TradeFair;
use Sheba\ModificationFields;

class TradeFairController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStores(Request $request)
    {
        try {
            $partners = DB::select('SELECT partners.id,partners.business_type
                                FROM   partners  JOIN (
                                SELECT  business_type, GROUP_CONCAT(id) grouped_partner
                                FROM  partners
                                where is_webstore_published = 1
                                GROUP BY business_type) group_max 
                                ON partners.business_type = group_max.business_type
                                AND FIND_IN_SET(id, grouped_partner) BETWEEN 1 AND 3
                                ORDER BY   partners.business_type DESC');

            $mapped_partner_business_type = [];
            foreach ($partners as $partner) {
                $mapped_partner_business_type[$partner->id] = $partner->business_type;
            }

            $business_types = constants('PARTNER_BUSINESS_TYPE');
            $converted_business_types = [];
            foreach ($business_types as $business_type) {
                $converted_business_types[$business_type['bn']] = $business_type['en'];
            }


            $partners = array_column($partners, 'id');

            $trade_fair = TradeFair::whereIn('partner_id', $partners)->get()->map(function ($shop) use ($mapped_partner_business_type, $converted_business_types) {
                return [
                    'stall_id' => $shop->stall_id,
                    'partner_id' => $shop->partner_id,
                    'desciption' => $shop->description,
                    'discount' => $shop->discount,
                    'is_published' => $shop->is_published,
                    'business_type' => $converted_business_types[$mapped_partner_business_type[$shop->partner_id]]
                ];
            });
            $data = [];
            $stores = [];
            $trade_fair = collect($trade_fair)->groupBy('business_type');
            foreach ($trade_fair as $key => $value) {
                $stores['business_type'] = $key;
                $stores['stores'] = $value;
                array_push($data, $stores);
            }

            return api_response($request, null, 200, ['data' => $data]);
        } catch (ModelNotFoundException $e) {
            app('sentry')->captureException($e);
            return response()->json(['code' => 404, 'message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

}