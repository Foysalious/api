<?php namespace App\Http\Controllers;

use App\Models\OfferShowcase;
use App\Transformers\CampaignTransformer;
use App\Transformers\OfferTransformer;
use Illuminate\Http\Request;
use App\Models\HyperLocal;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Throwable;

class CampaignController extends Controller
{

    public function index(Request $request)
    {
        try {
            $offers = OfferShowcase::active()->campaign()->valid()->orderBy('created_at', 'DESC')->get();

            $location = '';
            if ($request->filled('location')) {
                $location = (int)$request->location;
            } elseif ($request->filled('lat') && $request->filled('lng')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location_id;
            }

            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $campaigns = new Collection($offers, new OfferTransformer());
            $campaigns = $manager->createData($campaigns)->toArray()['data'];

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