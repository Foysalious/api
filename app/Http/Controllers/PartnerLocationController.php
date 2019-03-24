<?php

namespace App\Http\Controllers;

use App\Exceptions\HyperLocationNotFoundException;
use App\Models\Category;
use App\Models\CategoryPartner;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\Partner;
use App\Repositories\ReviewRepository;
use App\Sheba\Checkout\PartnerList;
use App\Sheba\Checkout\Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Dal\PartnerLocation\PartnerLocation;
use Sheba\Dal\PartnerLocation\PartnerLocationRepository;

class PartnerLocationController extends Controller
{
    private $reviewRepository;

    public function __construct()
    {
        $this->reviewRepository = new ReviewRepository();
    }

    public function getPartners(Request $request, PartnerListRequest $partnerListRequest)
    {
        try {
            $this->validate($request, [
                'date' => 'sometimes|required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'sometimes|required|string',
                'services' => 'required|string',
                'isAvailable' => 'sometimes|required',
                'partner' => 'sometimes|required',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'skip_availability' => 'numeric',
                'filter' => 'string|in:sheba',
            ]);
            $validation = new Validation($request);
            if (!$validation->isValid()) return api_response($request, $validation->message, 400, ['message' => $validation->message]);
            $partner = $request->has('partner') ? $request->partner : null;
            $partnerListRequest->setRequest($request)->prepareObject();
            $partner_list = new PartnerList();
            $partner_list->setPartnerListRequest($partnerListRequest)->find($partner);
            if ($request->has('isAvailable')) {
                $partners = $partner_list->partners;
                $available_partners = $partners->filter(function ($partner) {
                    return $partner->is_available == 1;
                });
                $is_available = count($available_partners) != 0 ? 1 : 0;
                return api_response($request, $is_available, 200, ['is_available' => $is_available, 'available_partners' => count($available_partners)]);
            }
            if ($partner_list->hasPartners) {
                $partner_list->addPricing();
                $partner_list->addInfo();
                if ($request->has('filter') && $request->filter == 'sheba') {
                    $partner_list->sortByShebaPartnerPriority();
                } else {
                    $partner_list->sortByShebaSelectedCriteria();
                }
                $partners = $partner_list->removeKeysFromPartner();
                return api_response($request, $partners, 200, ['partners' => $partners->values()->all()]);
            }
            return api_response($request, null, 404, ['message' => 'No partner found.']);
        } catch (HyperLocationNotFoundException $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 400, ['message' => 'Your are out of service area.']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getNearbyPartners(Request $request, PartnerLocationRepository $partnerLocationRepository)
    {
        try {
            $location = null;
            if ($request->has('location')) {
                $location = Location::find($request->location);
            } else if ($request->has('lat')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location;
            }

            if(!$location)
                return api_response($request, 'Invalid location', 400, ['message' => 'Invalid location']);

            $geo_info = json_decode($location->geo_informations);
            $nearByPartners = $partnerLocationRepository->findNearByPartners($geo_info->lat, $geo_info->lng);
            $nearByPartnerIds = $nearByPartners->pluck('partner_id');
            $partners = Partner::whereIn('id',$nearByPartnerIds)->get();

            $partnerDetails = collect();
            if($request->has('category_id')) {

                foreach ($partners as $partner) {
                    if(!in_array($request->category_id, $partner->servingMasterCategoryIds()))
                        continue;

                    $serving_master_categories = $partner->servingMasterCategories();
                    $detail = [
                        'name' => $partner->name,
                        'sub_domain' => $partner->sub_domain,
                        'serving_category' => $serving_master_categories,
                        'address' => $partner->address,
                        'logo' => $partner->logo,
                        'lat' => $nearByPartners->where('partner_id',$partner->id)->first()->location->coordinates[1],
                        'lng' => $nearByPartners->where('partner_id',$partner->id)->first()->location->coordinates[0],
                        'description' => $partner->description,
                        'badge' => $partner->resolveBadge(),
                        'rating' => round($this->reviewRepository->getAvgRating($partner->reviews)),
                        'distance' => round($nearByPartners->where('partner_id',$partner->id)->first()->distance, 2)
                    ];
                    $partnerDetails->push($detail);
                }
                return api_response($request, null, 200, [ 'partners' => $partnerDetails]);
            } else {
                foreach ($partners as $partner) {
                    $serving_master_categories = $partner->servingMasterCategories();
                    $detail = [
                        'name' => $partner->name,
                        'sub_domain' => $partner->sub_domain,
                        'serving_category' => $serving_master_categories,
                        'address' => $partner->address,
                        'logo' => $partner->logo,
                        'lat' => json_decode($partner->geo_informations, true)->lat,
                        'lng' => json_decode($partner->geo_informations, true)->lng,
                        'description' => $partner->description,
                        'badge' => $partner->resolveBadge(),
                        'rating' => round($this->reviewRepository->getAvgRating($partner->reviews)),
                        'distance' => round($nearByPartners->where('partner_id',$partner->id)->first()->distance, 2)
                    ];
                    $partnerDetails->push($detail);
                }
                //Find all partners in given location
                return api_response($request, null, 200, [ 'partners' => $partnerDetails]);
            }

        } catch (\Throwable $e) {app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }
}