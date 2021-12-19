<?php namespace App\Http\Controllers;

use Sheba\Dal\Category\Category;
use App\Models\CategoryGroup;
use App\Models\HomepageSetting;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\OfferGroup;
use App\Models\ScreenSettingElement;
use App\Sheba\Queries\Category\StartPrice;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferGroupController extends Controller
{
    public function show($offer_group, Request $request)
    {
        try {
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $location = null;
            if ($request->filled('location')) {
                $location = Location::find($request->location)->id;
            } else if ($request->filled('lat')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
            }

            if ($location) {
                $offer_group = OfferGroup::with(['offers' => function ($q) use ($location) {
                    return $q->active()->validFlashOffer()->flash()->select('id', 'target_type', 'target_id', 'start_date', 'end_date')->orderBy('end_date', 'asc')->take(6)
                        ->whereHas('locations', function ($q) use ($location) {
                            $q->where('locations.id', $location);
                        });
                }])->where('id', $offer_group)->select('id', 'name', 'app_thumb')->first();
            } else {
                $offer_group = OfferGroup::with(['offers' => function ($q) {
                    $q->active()->validFlashOffer()->flash()->select('id', 'target_type', 'target_id', 'start_date', 'end_date')->orderBy('end_date', 'asc')->take(6);
                }])->where('id', $offer_group)->select('id', 'name', 'app_thumb')->first();
            }

            $offers = [];
            if ($offer_group) {
                foreach ($offer_group->offers as $offer) {
                    $offer = [
                        "id" => $offer->id,
                        "target_type" => snake_case(explode('\\', $offer->target_type)[2]),
                        "target_id" => (int)$offer->target_id,
                        "start_time" => $offer->start_date->toDateTimeString(),
                        "end_time" => $offer->end_date->toDateTimeString(),
                    ];
                    array_push($offers, $offer);
                }

                $offer_group = [
                    'id' => $offer_group->id,
                    "name" => $offer_group->name,
                    "app_thumb" => $offer_group->app_thumb,
                    "banner" => $offer_group->banner,
                    "offers" => $offers
                ];
                return api_response($request, $offer_group, 200, ['offer_group' => $offer_group]);
            } else {
                return api_response($request, 1, 404);
            }

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}