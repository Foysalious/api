<?php namespace App\Http\Controllers;

use App\Models\OfferShowcase;
use Illuminate\Http\Request;
use App\Models\HyperLocal;
use Throwable;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        try {
            $offers = OfferShowcase::active()->campaign()->valid()->orderBy('created_at', 'DESC')->get();

            $location = '';
            if ($request->has('location')) {
                $location = (int)$request->location;
            } elseif ($request->has('lat') && $request->has('lng')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location_id;
            }

            $campaigns = [];
            foreach ($offers as $offer) {
                $target_type = null;
                if ($offer->target_type) {
                    $target_type = explode('\\', $offer->target_type);
                    $target_type = snake_case(end($target_type));
                }
                $campaign = [
                    "target_type" => $target_type,
                    "target_id" => $offer->target_id,
                    "title" => $offer->title ?: null,
                    "description" => $offer->detail_description ?: null,
                    "image" => $offer->app_banner ?: ($offer->app_thumb ?: null),
                ];
                array_push($campaigns, $campaign);
            }

            if (count($campaigns) > 0) {
                return api_response($request, $campaigns, 200, ['campaigns' => $campaigns]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }


    }
}