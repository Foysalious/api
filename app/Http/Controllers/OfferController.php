<?php namespace App\Http\Controllers;

use Sheba\Dal\Category\Category;
use App\Models\Customer;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\OfferShowcase;
use App\Models\User;
use App\Transformers\OfferDetailsTransformer;
use App\Transformers\OfferTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Offer\OfferFilter;
use Illuminate\Validation\ValidationException;
use Throwable;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'location' => 'sometimes|numeric',
            'user' => 'numeric',
            'user_type' => 'string|in:customer',
            'remember_token' => 'string',
            'category' => 'numeric',
            'lat' => 'sometimes|numeric',
            'lng' => 'required_with:lat'
        ]);

        if ($request->has('user') && $request->user == 0) return api_response($request, null, 404);
        $user = $category = $location = null;
        if ($request->has('user') && $request->has('user_type') && $request->has('remember_token')) {
            $model_name = "App\\Models\\" . ucwords($request->user_type);
            $user = $model_name::with('orders', 'promotions')->where('id', (int)$request->user)->where('remember_token', $request->remember_token)->first();
        }
        if ($request->has('location')) {
            $location = Location::find($request->location);
        } else if ($request->has('lat')) {
            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyperLocation)) $location = $hyperLocation->location;
        }
        $offers = OfferShowcase::active()->valid()->actual()
            ->notCampaign()
            ->orderBy('end_date')->get();
        if (count($offers) == 0) return api_response($request, null, 404);
        $offer_filter = new OfferFilter($offers);
        if ($user) $offer_filter->setCustomer($user);
        if ($request->has('category')) $offer_filter->setCategory(Category::find((int)$request->category));
        if ($location) $offer_filter->setLocation($location);
        $offers = $this->getOffersWithFormation($offer_filter->filter()->sortByDesc('amount'));
        if (count($offers) > 0) return api_response($request, $offers, 200, ['offers' => $offers]);
        else return api_response($request, null, 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getPartnerOffer(Request $request)
    {
        try {
            $this->validate($request, [
                'remember_token' => 'required_unless:user,0|string',
            ]);
            $offers = OfferShowcase::active()->valid()->actual()
                ->notCampaign()
                ->getPartnerOffers()
                ->orderBy('end_date')->get();
            $offer_filter = new OfferFilter($offers);
            $offers = $this->getOffersWithFormation($offer_filter->filter()->sortByDesc('id'));
            if (count($offers) > 0) return api_response($request, $offers, 200, ['offers' => $offers]);
            else return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $offers
     * @return mixed
     */
    private function getOffersWithFormation($offers)
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $resource = new Collection($offers, new OfferTransformer());
        return $manager->createData($resource)->toArray()['data'];
    }

    public function show($offer, Request $request)
    {
        try {
            $offer = OfferShowcase::active()->where('id', $offer)->first();
            if ($offer) {
                $customer = $request->has('remember_token') ? Customer::where('remember_token', $request->input('remember_token'))->first() : null;
                if ($customer) {
                    $offer->customer_id = $customer->id;
                }
                $manager = new Manager();
                $manager->setSerializer(new ArraySerializer());
                $data = $manager->createData((new Item($offer, new OfferDetailsTransformer())))->toArray();
                return api_response($request, $offer, 200, ['offer' => $data]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}