<?php namespace App\Http\Controllers\Partner\Webstore;


use App\Http\Controllers\Controller;
use App\Sheba\Partner\TradeFair\TradeFair;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;

class TradeFairController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @param TradeFair $trade_fair
     * @return JsonResponse
     */
    public function getStores(Request $request, TradeFair $trade_fair)
    {
        try {
            $partners = $trade_fair->getBusinessTypeWisePartner();
            $mapped_partner_business_type = [];
            foreach ($partners as $partner) {
                $mapped_partner_business_type[$partner->id] = $partner->business_type;
            }
            $partners = array_column($partners, 'id');
            $trade_fair_data = $trade_fair->makeData($partners,$mapped_partner_business_type);

            return api_response($request, null, 200, ['data' => $trade_fair_data]);
        } catch (ModelNotFoundException $e) {
            app('sentry')->captureException($e);
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @param TradeFair $trade_fair
     * @return JsonResponse
     */
    public function getStoresByBusinessType(Request $request, TradeFair $trade_fair)
    {
        try{
            $business_types = constants('PARTNER_BUSINESS_TYPE');

            $en_business_types = [];
            collect($business_types)->each(function ($type) use (&$en_business_types) {
                array_push($en_business_types, $type['en']);
            });
            $en_business_types = implode(',', $en_business_types);
            $this->validate($request, [
                'business_type' => "required|in:$en_business_types"
            ]);
            $stores = $trade_fair->getStoresByBusinessType($request->business_type);
            return api_response($request, null, 200, ['stores' => $stores]);

        }catch (ValidationException $e) {
            app('sentry')->captureException($e);
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (ModelNotFoundException $e) {
            app('sentry')->captureException($e);
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}