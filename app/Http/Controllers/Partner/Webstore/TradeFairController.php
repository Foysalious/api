<?php namespace App\Http\Controllers\Partner\Webstore;


use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
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
            $typewise_partners = Partner::has('tradeFair')->with('tradeFair')->where('is_webstore_published', 1)->whereNotNull('business_type')->select('id', 'business_type')
                ->get()
                ->groupBy('business_type')
                ->map(function ($partner) {
                    return $partner->take(3);
                });

            $data = [];
            $business_types = constants('PARTNER_BUSINESS_TYPE');
            $converted_business_types = [];
            foreach ($business_types as $business_type) {
                $converted_business_types[$business_type['bn']] = $business_type['en'];
            }

            foreach ($typewise_partners as $key => $partners) {
                $temp = [];
                $temp['business_type'] = $converted_business_types[$key];
                $temp['stores'] = [];
                foreach ($partners as $partner) {
                    array_push($temp['stores'], [
                        'partner_id' => $partner->id,
                        'stall_id' => $partner->tradeFair->stall_id,
                        'description' => $partner->tradeFair->description,
                        'discount' => $partner->tradeFair->discount,
                        'is_published' => $partner->tradeFair->is_published
                    ]);
                }
                array_push($data, $temp);
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